<?php

namespace App\Services;

use App\Models\Order;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class PaymobService
{
    protected $apiKey;
    protected $baseUrl;
    protected $client;

    public function __construct()
    {
        $this->apiKey = config('services.paymob.api_key'); // Add this to your .env
        $this->baseUrl = 'https://accept.paymob.com/api'; // Paymob's base API URL
        $this->client = new Client();
    }

    public function authenticate()
    {
        $response = $this->client->post("{$this->baseUrl}/auth/tokens", [
            'json' => [
                'api_key' => $this->apiKey,
            ],
        ]);

        return json_decode($response->getBody(), true)['token'];
    }

    public function createOrder($authToken, $orderData)
    {
        $response = $this->client->post("{$this->baseUrl}/ecommerce/orders", [
            'headers' => [
                'Authorization' => "Bearer $authToken",
            ],
            'json' => $orderData,
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getPaymentKey($authToken, $paymentData)
    {
        $response = $this->client->post("{$this->baseUrl}/acceptance/payment_keys", [
            'headers' => [
                'Authorization' => "Bearer $authToken",
            ],
            'json' => $paymentData,
        ]);

        return json_decode($response->getBody(), true)['token'];
    }

    public function createPayment(Order $order, array $data)
    {
        // Step 1: Authenticate with Paymob
        $authToken = $this->authenticate();

        // $merchantId = config('services.paymob.merchant_id');
        // if (!$merchantId) {
        //     throw new \Exception('Merchant ID is not configured');
        // }

        // $data = [
        //     'amount' => 10,
        //     'first_name' => 'John',
        //     'last_name' => 'Doe',
        //     'email' => 'a@a.com',
        //     'phone' => '0123456789',
        // ];
        // Step 2: Create an Order
        $items = [];
        foreach ($order->items as $item) {
            $items[] = [
                'name' => $item->tennisCourt->name,
                'amount_cents' => $item->price * 100,
                'description' => 'Tennis Court Reservation Payment',
                'quantity' => 1
            ];
        }
        $orderData = [
            'merchant_order_id' => uniqid(),
            'amount_cents' => $data['amount'] * 100,
            'currency' => 'EGP',
            'delivery_needed' => false,
            // 'merchant_id' => config('services.paymob.merchant_id'),
            'items' => $items,
        ];
        Log::info('orderData: ' . json_encode($orderData));

        $paymentOrder = $this->createOrder($authToken, $orderData);

        // Step 3: Payment Key Request
        $paymentData = [
            'auth_token' => $authToken,
            'amount_cents' => $data['amount'] * 100,
            'expiration' => 3600,
            'order_id' => $paymentOrder['id'],
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
        $order->update([
            'payment_order_id' => $paymentOrder['id']
        ]);
        $paymentKey = $this->getPaymentKey($authToken, $paymentData);
        Log::info('Payment Key: ' . $paymentKey);
        Log::info('Payment Data: ' . json_encode($paymentData));

        // Step 4: Redirect to the Payment Page (Iframe URL)
        $iframeId = config('services.paymob.iframe_id');
        $iframeUrl = "https://accept.paymob.com/api/acceptance/iframes/$iframeId?payment_token=$paymentKey";

        return $iframeUrl;
    }
}
