<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index()
    {
        $invoices = [];
        try {
            $response = $this->getEposInvoiceList();
            $invoices = json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
            $body = $response ? $response->getBody()->getContents() : '';
            session()->flash('error', 'Ошибка при получении списка счетов: ' . ($body ?: $e->getMessage()));
        } catch (\Exception $e) {
            session()->flash('error', 'Ошибка при получении списка счетов: ' . $e->getMessage());
        }

        return view('admin.orders.index', compact('invoices'));
    }

    public function create()
    {
        return view('admin.orders.create');
    }

    public function completeApiRequest(Request $request)
    {
//        $data = $request->all();


//        $response = $this->sendRequest('/api/epos-invoice/list', $data);

        $response = $this->getEposInvoiceList();


        dd($response);
    }

    protected function sendRequest($uri, $data)
    {
        $baseUrl = config('services.webkassa.base_url');

        $httpClient = new Client(
            [
                'base_uri' => $baseUrl,
                // You can set any number of default request options.
                'timeout'  => 2.0,
            ]
        );

        $response = $httpClient->request(
            'POST', $uri,
            [
                RequestOptions::HEADERS => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . config('services.webkassa.token'),
                ],
                RequestOptions::FORM_PARAMS => $data
            ]
        );

        return $response;
    }

    public function getEposInvoiceList(?Carbon $dateFrom = null, ?Carbon $dateTo = null, bool $filterByPaymentDate = false)
    {
        $baseUrl = config('services.webkassa.base_url');
        $httpClient = new Client(['timeout' => 30.0]);

//        if ($filterByPaymentDate) {
//            // Фильтр paymentDateFrom/To на API не работает — возвращает []. Берём все оплаченные и фильтруем локально.
//            $data = [
//                'invoiceDateFrom' => Carbon::now()->subYears(2)->getTimestampMs(),
//                'invoiceDateTo' => Carbon::now()->addMonth()->getTimestampMs(),
//                'paymentStatus' => 30, // 30 = успешная оплата
//            ];
//        } else {
            $data = [
//                'paymentStatus' => 3,
                'invoiceId' => 28230
//                'paymentDateFrom' => $dateFrom->getTimestampMs(),
//                'paymentDateTo' => $dateTo->getTimestampMs(),
            ];
//        }

        $url = $baseUrl . '/api/epos-invoice/list';
        Log::channel('single')->info('WebKassa API Request', [
            'url' => $url,
            'method' => 'POST',
            'body' => $data,
        ]);

        try {
            $response = $httpClient->post($url, [
                RequestOptions::HEADERS => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . config('services.webkassa.token'),
                ],
                RequestOptions::JSON => $data
            ]);


//            $body = $response->getBody()->getContents();

            dd($response->getBody()->getContents());
            return $response;

            $bodyPreview = strlen($body) > 2000 ? substr($body, 0, 2000) . '...[truncated]' : $body;
            Log::channel('single')->info('WebKassa API Response', [
                'url' => $url,
                'status' => $response->getStatusCode(),
                'body_preview' => $bodyPreview,
                'body_length' => strlen($body),
            ]);
            $response->getBody()->rewind();

            return $response;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
            $errorBody = $response ? $response->getBody()->getContents() : '';
            if ($response) {
                $response->getBody()->rewind();
            }
            Log::channel('single')->error('WebKassa API Error', [
                'url' => $url,
                'message' => $e->getMessage(),
                'status' => $response?->getStatusCode(),
                'body' => $errorBody,
            ]);
            throw $e;
        }
    }

    public function getEposPayerList()
    {
        $baseUrl = config('services.webkassa.base_url');
        $httpClient = new Client(['timeout' => 10.0]);

        $url = $baseUrl . '/api/epos-payer/list';
        $data = (object) [];

        Log::channel('single')->info('WebKassa API Request', [
            'url' => $url,
            'method' => 'POST',
            'body' => $data,
        ]);

        try {
            $response = $httpClient->post($url, [
                RequestOptions::HEADERS => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . config('services.webkassa.token'),
                ],
                RequestOptions::JSON => $data
            ]);

            $body = $response->getBody()->getContents();
            $bodyPreview = strlen($body) > 2000 ? substr($body, 0, 2000) . '...[truncated]' : $body;
            Log::channel('single')->info('WebKassa API Response', [
                'url' => $url,
                'status' => $response->getStatusCode(),
                'body_preview' => $bodyPreview,
                'body_length' => strlen($body),
            ]);
            $response->getBody()->rewind();

            return $response;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
            $errorBody = $response ? $response->getBody()->getContents() : '';
            if ($response) {
                $response->getBody()->rewind();
            }
            Log::channel('single')->error('WebKassa API Error', [
                'url' => $url,
                'message' => $e->getMessage(),
                'status' => $response?->getStatusCode(),
                'body' => $errorBody,
            ]);
            throw $e;
        }
    }

    public function payers()
    {
        $payers = [];
        $fallbackNotice = null;

        try {
            $response = $this->getEposPayerList();
            $payers = json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Обход: API /epos-payer/list возвращает 500 из‑за бага WebKassa (поле "name" vs "payerName").
            // Получаем плательщиков из списка счетов.
            try {
                $invoiceResponse = $this->getEposInvoiceList();
                $invoices = json_decode($invoiceResponse->getBody()->getContents(), true) ?? [];
                $payersById = [];
                foreach ($invoices as $inv) {
                    $id = $inv['payerId'] ?? null;
                    if ($id && !isset($payersById[$id])) {
                        $payersById[$id] = [
                            'payerId' => $id,
                            'payerName' => $inv['payerName'] ?? '-',
                            'email' => $inv['email'] ?? '-',
                            'phone' => $inv['phone'] ?? '-',
                            'payerInformation' => null,
                            'viberPermissionName' => '-',
                        ];
                    }
                }
                $payers = array_values($payersById);
                $fallbackNotice = 'Список получен из счетов E-POS (эндпоинт /epos-payer/list временно недоступен).';
                session()->forget('error');
            } catch (\Exception $inner) {
                $response = $e->getResponse();
                $body = $response ? $response->getBody()->getContents() : '';
                session()->flash('error', 'Ошибка: ' . ($body ?: $e->getMessage()));
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Ошибка при получении списка плательщиков: ' . $e->getMessage());
        }

        return view('admin.orders.payers', compact('payers', 'fallbackNotice'));
    }

    public function invoicesByDate(Request $request)
    {
        $dateStr = $request->query('date');
        $date = $dateStr ? Carbon::parse($dateStr) : Carbon::today();
        $dateFrom = $date->copy()->startOfDay();
        $dateTo = $date->copy()->endOfDay();

        $invoices = [];

        $response = $this->getEposInvoiceList($dateFrom, $dateTo, filterByPaymentDate: true);

        dd($response);

        return view('admin.orders.by-date', compact('invoices', 'date'));
    }
}
