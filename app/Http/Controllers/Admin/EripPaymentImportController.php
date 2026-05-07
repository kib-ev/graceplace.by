<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EripPayment;
use App\Models\Master;
use App\Services\Erip\EripMonthlyReportParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EripPaymentImportController extends Controller
{
    public function index()
    {
        request()->validate([
            'erip_date' => ['nullable', 'date'],
        ]);

        $eripDate = request('erip_date') ?: now()->toDateString();

        $payments = EripPayment::query()
            ->withCount('allocations')
            ->whereDate('paid_at', $eripDate)
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->paginate(100)
            ->withQueryString();

        $mastersByPhone = Master::query()
            ->with('user')
            ->get()
            ->reduce(function ($carry, Master $master) {
                $normalizedPhone = preg_replace('/\D+/', '', (string) ($master->user?->phone ?? ''));
                if ($normalizedPhone !== '' && ! isset($carry[$normalizedPhone])) {
                    $carry[$normalizedPhone] = $master;
                }

                return $carry;
            }, []);

        $payments->setCollection(
            $payments->getCollection()->map(function (EripPayment $payment) use ($mastersByPhone) {
                $normalizedPayerPhone = preg_replace('/\D+/', '', (string) ($payment->payer_phone ?? ''));
                $payment->matchedMaster = $mastersByPhone[$normalizedPayerPhone] ?? null;

                return $payment;
            })
        );

        return view('admin.erip-imports.index', compact('payments', 'eripDate'));
    }

    public function store(Request $request, EripMonthlyReportParser $parser)
    {
        $request->validate([
            'report' => [
                'required',
                'file',
                'max:20480',
                function ($attribute, $value, $fail) {
                    $extension = strtolower($value->getClientOriginalExtension());
                    if ($extension !== 'xlsx') {
                        $fail('The report field must be a file of type: xlsx.');
                    }
                },
            ],
        ]);

        $uploaded = $request->file('report');
        $parsed = $parser->parse($uploaded->getPathname());

        $inserted = 0;
        $skipped = 0;

        DB::transaction(function () use ($parsed, &$inserted, &$skipped) {
            foreach ($parsed['rows'] as $row) {
                $fingerprint = sha1(json_encode([
                    $row['operation_number'],
                    $row['paid_at'],
                    $row['amount'],
                    $row['payer_raw'],
                ], JSON_UNESCAPED_UNICODE));

                if (EripPayment::where('fingerprint', $fingerprint)->exists()) {
                    $skipped++;

                    continue;
                }

                EripPayment::create([
                    'row_number' => $row['row_number'],
                    'status' => $row['status'],
                    'amount' => $row['amount'],
                    'net_amount' => $row['net_amount'],
                    'paid_at' => $row['paid_at'],
                    'operation_number' => $row['operation_number'],
                    'payment_method' => $row['payment_method'],
                    'payer_raw' => $row['payer_raw'],
                    'payer_phone' => $row['payer_phone'],
                    'payer_name' => $row['payer_name'],
                    'invoice_created_at' => $row['invoice_created_at'],
                    'account_number' => $row['account_number'],
                    'terminal_sn' => $row['terminal_sn'],
                    'merchant_code' => $row['merchant_code'],
                    'raw_row' => $row['raw_row'],
                    'fingerprint' => $fingerprint,
                ]);
                $inserted++;
            }
        });

        return redirect()
            ->route('admin.erip-imports.index', ['erip_date' => now()->toDateString()])
            ->with('success', "Импорт завершен: добавлено {$inserted}, пропущено дублей {$skipped}.");
    }

    public function destroy(Request $request, EripPayment $payment)
    {
        if ($payment->allocations()->exists()) {
            return redirect()
                ->route('admin.erip-imports.index', array_filter([
                    'erip_date' => $request->input('erip_date'),
                ]))
                ->withErrors(['payment' => 'Нельзя удалить платеж: он уже привязан к записи.']);
        }

        $payment->delete();

        return redirect()
            ->route('admin.erip-imports.index', array_filter([
                'erip_date' => $request->input('erip_date'),
            ]))
            ->with('success', 'Платеж удален.');
    }
}
