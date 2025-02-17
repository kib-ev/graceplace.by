<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    const TOKEN = 'eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiI2MEJEMEI4MS02NzRELTRENDktODUzNC0zNDExODgyMzk0RkIiLCJleHAiOjE3Njk2ODI4MDJ9.o15P1CUui5lDJSfHO_5hPr1XY4epK_xI1y5nvhKtlBKIK2VNndFSx7ANf1MCsHum7EgFINnXiUfS10kaskw39w';
    public function index() {


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
        $token = 'eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiI2MEJEMEI4MS02NzRELTRENDktODUzNC0zNDExODgyMzk0RkIiLCJleHAiOjE3Njk2ODI4MDJ9.o15P1CUui5lDJSfHO_5hPr1XY4epK_xI1y5nvhKtlBKIK2VNndFSx7ANf1MCsHum7EgFINnXiUfS10kaskw39w';
        $baseUrl = 'https://cabinet.webkassa.by';
//        $baseUrl = 'https://cabinet.rdigital.by';

        $httpClient = new Client(
            [
                // Base URI is used with relative requests
                'base_uri' => 'https://cabinet.webkassa.by',
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
                    'Authorization' => 'Bearer ' . $token,
                ],
                RequestOptions::FORM_PARAMS => $data
            ]
        );

        return $response;
    }

    public function getEposInvoiceList()
    {
        $httpClient = new Client();

        $data = [
            'invoiceId' => 1,
            'invoiceStatus' => 3,
            'invoiceDateFrom' => now()->subDays(365)->getTimestampMs(),
            'invoiceDateTo' => now()->endOfMonth()->getTimestampMs(),
            'paymentDateFrom' => now()->subYears(10)->getTimestampMs(),
            'paymentDateTo' => now()->addYears(10)->getTimestampMs(),
            'payerId' => 94,
            'payerGroupId' => 9,
            'payerName' => '',
        ];

        dump($data);

        $response = $httpClient->post(
//            'https://cabinet.webkassa.by/api/epos-invoice/list',
            'https://graceplace.by/test',
            [
                RequestOptions::HEADERS => [
//                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . self::TOKEN,
                ],
                RequestOptions::JSON => $data
            ]
        );

        return $response;
    }
}
