<form action="{{ route('paymob.payment') }}" method="GET">
    <input type="text" name="first_name" placeholder="First Name" required>
    <input type="text" name="last_name" placeholder="Last Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="phone" placeholder="Phone Number" required>
    <input type="number" name="amount" placeholder="Amount" required>
    <button type="submit">Pay Now</button>
</form>
