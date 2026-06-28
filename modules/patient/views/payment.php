<?php
/**
 * Payment Page
 * Palliative Care System
 */

// Set page title
$page_title = 'Payment';

// Include header
require_once __DIR__ . '/../../../views/includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Payment Details</h4>
                </div>
                <div class="card-body">
                    <!-- Order Summary -->
                    <div class="mb-4">
                        <h5>Order Summary</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th width="50%">Payment For</th>
                                        <td>
                                            <?php
                                            switch($payment_type) {
                                                case 'medicine_order':
                                                    echo 'Medicine Order #' . $reference_id;
                                                    break;
                                                case 'cab_booking':
                                                    echo 'Cab Booking #' . $reference_id;
                                                    break;
                                                case 'appointment':
                                                    echo 'Appointment #' . $reference_id;
                                                    break;
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Amount</th>
                                        <td>₹<?php echo isset($amount) ? number_format($amount, 2) : '0.00'; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <form id="paymentForm" action="index.php?module=patient&action=process_payment" method="POST">
                        <input type="hidden" name="payment_type" value="<?php echo htmlspecialchars($payment_type); ?>">
                        <input type="hidden" name="reference_id" value="<?php echo htmlspecialchars($reference_id); ?>">
                        <input type="hidden" name="amount" value="<?php echo isset($amount) ? htmlspecialchars($amount) : '0.00'; ?>">

                        <div class="mb-4">
                            <h5>Payment Method</h5>
                            <div class="payment-methods">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method" id="creditCard" value="credit_card" checked>
                                    <label class="form-check-label" for="creditCard">
                                        <i class="fas fa-credit-card"></i> Credit Card
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method" id="debitCard" value="debit_card">
                                    <label class="form-check-label" for="debitCard">
                                        <i class="fas fa-credit-card"></i> Debit Card
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method" id="upi" value="upi">
                                    <label class="form-check-label" for="upi">
                                        <i class="fas fa-mobile-alt"></i> UPI
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method" id="netBanking" value="net_banking">
                                    <label class="form-check-label" for="netBanking">
                                        <i class="fas fa-university"></i> Net Banking
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Credit/Debit Card Details -->
                        <div id="cardDetails" class="card-payment-details">
                            <div class="mb-3">
                                <label for="cardNumber" class="form-label">Card Number</label>
                                <input type="text" class="form-control" id="cardNumber" name="card_number" placeholder="1234 5678 9012 3456">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="expiryDate" class="form-label">Expiry Date</label>
                                    <input type="text" class="form-control" id="expiryDate" name="expiry_date" placeholder="MM/YY">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="password" class="form-control" id="cvv" name="cvv" placeholder="123">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="cardName" class="form-label">Name on Card</label>
                                <input type="text" class="form-control" id="cardName" name="card_name" placeholder="John Doe">
                            </div>
                        </div>

                        <!-- UPI Details -->
                        <div id="upiDetails" class="upi-payment-details" style="display: none;">
                            <div class="mb-3">
                                <label for="upiId" class="form-label">UPI ID</label>
                                <input type="text" class="form-control" id="upiId" name="upi_id" placeholder="username@upi">
                            </div>
                        </div>

                        <!-- Net Banking Details -->
                        <div id="netBankingDetails" class="netbanking-payment-details" style="display: none;">
                            <div class="mb-3">
                                <label for="bank" class="form-label">Select Bank</label>
                                <select class="form-select" id="bank" name="bank">
                                    <option value="">Select a bank</option>
                                    <option value="sbi">State Bank of India</option>
                                    <option value="hdfc">HDFC Bank</option>
                                    <option value="icici">ICICI Bank</option>
                                    <option value="axis">Axis Bank</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Pay ₹<?php echo isset($amount) ? number_format($amount, 2) : '0.00'; ?>
                            </button>
                            <a href="#" class="btn btn-outline-secondary" onclick="history.back()">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle payment method details
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Hide all payment details sections
        document.querySelectorAll('.card-payment-details, .upi-payment-details, .netbanking-payment-details')
            .forEach(el => el.style.display = 'none');

        // Show the selected payment method details
        switch(this.value) {
            case 'credit_card':
            case 'debit_card':
                document.getElementById('cardDetails').style.display = 'block';
                break;
            case 'upi':
                document.getElementById('upiDetails').style.display = 'block';
                break;
            case 'net_banking':
                document.getElementById('netBankingDetails').style.display = 'block';
                break;
        }
    });
});

// Form validation
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Add your payment validation logic here
    
    // Submit form if validation passes
    this.submit();
});
</script>

<?php
// Include footer
require_once __DIR__ . '/../../../views/includes/footer.php';
?> 