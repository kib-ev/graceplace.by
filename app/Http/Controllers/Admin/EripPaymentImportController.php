<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EripPayment;
use App\Models\EripPaymentImport;
use App\Services\Erip\EripMonthlyReportParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EripPaymentImportController extends Controller
{
    public function index()
    {
        $imports = EripPaymentImport::query()
            ->with('importedBy')
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        $totalPayments = EripPayment::count();
        $latestPaidAt = EripPayment::max('paid_at');

        return view('admin.erip-imports.index', compact('imports', 'totalPayments', 'latestPaidAt'));
    }

    public function store(Request $request, EripMonthlyReportParser $parser)
    {
        $request->validate([
            'report' => ['required', 'file', 'mimes:xlsx', 'max:20480'],
        ]);

        $uploaded = $request->file('report');
        $parsed = $parser->parse($uploaded->getPathname());

        $import = null;

        DB::transaction(function () use (&$import, $parsed, $uploaded) {
            $import = EripPaymentImport::create([
                'original_filename' => $uploaded->getClientOriginalName(),
                'report_month' => $parsed['report_month'],
                'imported_by_user_id' => auth()->id(),
                'rows_total' => count($parsed['rows']),
                'rows_inserted' => 0,
                'rows_skipped' => 0,
            ]);

            $inserted = 0;
            $skipped = 0;

            foreach ($parsed['rows'] as $row) {
                $fingerprint = sha1(json_encode([
                    $row['operation_number'],
                    $row['paid_at'],
                    $row['amount'],
                    $row['payer_raw'],
                ], JSON_UNESCAPED_UNICODE));

                $exists = EripPayment::where('fingerprint', $fingerprint)->exists();
                if ($exists) {
                    $skipped++;
                    continue;
                }

                EripPayment::create([
                    'erip_payment_import_id' => $import->id,
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

            $import->update([
                'rows_inserted' => $inserted,
                'rows_skipped' => $skipped,
            ]);
        });

        return redirect()
            ->route('admin.erip-imports.show', $import)
            ->with('success', "Импорт завершен: добавлено {$import->rows_inserted}, пропущено дублей {$import->rows_skipped}.");
    }

    public function show(EripPaymentImport $eripImport)
    {
        $payments = $eripImport->payments()
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->paginate(100);

        return view('admin.erip-imports.show', [
            'import' => $eripImport->load('importedBy'),
            'payments' => $payments,
        ]);
    }
}
