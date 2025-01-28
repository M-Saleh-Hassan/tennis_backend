<?php

namespace App\Http\Controllers;

use App\Services\PaymobService;
use Illuminate\Http\Request;

class PaymobController extends Controller
{
    protected $paymobService;

    public function __construct(PaymobService $paymobService)
    {
        $this->paymobService = $paymobService;
    }

    public function createPayment(Request $request)
    {
        // Step 1: Authenticate with Paymob
        $authToken = $this->paymobService->authenticate();

        // $merchantId = config('services.paymob.merchant_id');
        // if (!$merchantId) {
        //     throw new \Exception('Merchant ID is not configured');
        // }

        $data = [
            'amount' => 10,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'a@a.com',
            'phone' => '0123456789',
        ];
        // Step 2: Create an Order
        $orderData = [
            'merchant_order_id' => uniqid(),
            'amount_cents' => $data['amount'] * 100,
            'currency' => 'EGP',
            'delivery_needed' => false,
            // 'merchant_id' => config('services.paymob.merchant_id'),
            'items' => [
                [
                    'name' => 'Tennis Court Reservation',
                    'amount_cents' => $data['amount'] * 100,
                    'description' => 'Tennis Court Reservation Payment',
                    'quantity' => 1
                ]
            ],
        ];

        $order = $this->paymobService->createOrder($authToken, $orderData);

        // Step 3: Payment Key Request
        $paymentData = [
            'auth_token' => $authToken,
            'amount_cents' => $data['amount'] * 100,
            'expiration' => 3600,
            'order_id' => $order['id'],
            'billing_data' => [
                'apartment' => 'NA',
                'email' => $data['email'],
                'floor' => 'NA',
                'first_name' => $data['first_name'],
                'street' => 'NA',
                'building' => 'NA',
                'phone_number' => $data['phone'],
                'shipping_method' => 'NA',
                'postal_code' => 'NA',
                'city' => 'NA',
                'country' => 'EG',
                'last_name' => $data['last_name'],
                'state' => 'NA'
            ],
            'currency' => 'EGP',
            'integration_id' => config('services.paymob.integration_id'),
            'lock_order_when_paid' => true
        ];

        $paymentKey = $this->paymobService->getPaymentKey($authToken, $paymentData);

        // Step 4: Redirect to the Payment Page (Iframe URL)
        $iframeId = config('services.paymob.iframe_id');
        $iframeUrl = "https://accept.paymob.com/api/acceptance/iframes/$iframeId?payment_token=$paymentKey";

        return view('payment.iframe', [
            'iframeUrl' => $iframeUrl,
            'amount' => $data['amount'],
            'orderDetails' => [
                'id' => $order['id'],
                'amount' => $data['amount'],
                'currency' => 'EGP'
            ]
        ]);
    }
}
