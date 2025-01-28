<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymobWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Log the callback data for debugging
        Log::info('Paymob Callback Received:', $request->all());

        // // Verify the signature to ensure the request is from Paymob
        // $hmac = $request->input('hmac');
        // $calculatedHmac = hash_hmac('sha512', json_encode($request->all()), env('PAYMOB_HMAC_SECRET'));
        // Log::info($calculatedHmac . ' ' .$hmac);
        // if ($hmac !== $calculatedHmac) {
        //     Log::error('Invalid HMAC signature');
        //     return response()->json(['message' => 'Invalid signature'], 400);
        // }

        // Process the payment status
        $paymentStatus = $request->input('obj.success') ? 'paid' : 'cancelled';
        $orderId = $request->input('obj.order.id');

        // Example: Update your database based on the payment status
        // Assuming you have an Order model
        $order = Order::where('payment_order_id', $orderId)->first();

        if ($order) {
            $order->payment_data = $request->all();
            $order->paid_at = now();
            $order->status = $paymentStatus;
            $order->save();
        }

        // Return a response to acknowledge receipt of the callback
        return response()->json(['message' => 'Callback processed successfully'], 200);
    }
}
