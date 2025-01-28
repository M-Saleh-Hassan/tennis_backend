<!DOCTYPE html>
<html>
<head>
    <title>Payment</title>
</head>
<body>
    <div>
        <h2>Order Details</h2>
        <p>Amount: {{ $amount }} EGP</p>
        <p>Order ID: {{ $orderDetails['id'] }}</p>
    </div>

    <iframe
        src="{{ $iframeUrl }}"
        width="100%"
        height="600px"
        frameborder="0">
    </iframe>
</body>
</html>
