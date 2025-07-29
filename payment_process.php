<?php
// Include functions
require_once 'includes/functions.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);

// Check if required data is provided
if (!isset($data['order_id']) || !isset($data['payment_method'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required data']);
    exit;
}

// Get order ID and payment method
$order_id = $data['order_id'];
$payment_method = $data['payment_method'];

// Get order details
$order = get_order($order_id);

// Check if order exists
if (!$order) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Order not found']);
    exit;
}

// Process payment based on payment method
$payment_success = false;
$payment_details = [];

try {
    switch ($payment_method) {
        case 'paypal':
            // Process PayPal payment
            if (!isset($data['payment_id']) || !isset($data['payment_status'])) {
                throw new Exception('Missing PayPal payment data');
            }
            
            // PayPal configuration
            $paypal_config = [
                'sandbox' => true, // Set to false for production
                'client_id' => 'YOUR_PAYPAL_CLIENT_ID', // Replace with your PayPal client ID
                'client_secret' => 'YOUR_PAYPAL_CLIENT_SECRET', // Replace with your PayPal client secret
            ];
            
            // Verify payment with PayPal API (simplified for this example)
            // In a real implementation, you would make an API call to PayPal to verify the payment
            if ($data['payment_status'] === 'COMPLETED') {
                $payment_success = true;
                $payment_details = [
                    'transaction_id' => $data['payment_id'],
                    'status' => $data['payment_status'],
                    'amount' => $data['payment_amount'],
                    'currency' => $data['payment_currency'],
                    'payment_method' => 'paypal',
                    'payment_date' => date('Y-m-d H:i:s')
                ];
            } else {
                throw new Exception('PayPal payment not completed');
            }
            break;
            
        case 'stripe':
            // Process Stripe payment
            if (!isset($data['stripe_token'])) {
                throw new Exception('Missing Stripe token');
            }
            
            // Stripe configuration
            $stripe_config = [
                'secret_key' => 'YOUR_STRIPE_SECRET_KEY', // Replace with your Stripe secret key
                'currency' => 'usd'
            ];
            
            // In a real implementation, you would use the Stripe PHP SDK to create a charge
            // This is a simplified example
            $stripe_token = $data['stripe_token'];
            $amount = $data['amount'];
            $currency = $data['currency'];
            
            // Simulate a successful Stripe payment
            $payment_success = true;
            $payment_details = [
                'transaction_id' => 'stripe_' . uniqid(),
                'status' => 'succeeded',
                'amount' => $amount,
                'currency' => $currency,
                'payment_method' => 'stripe',
                'payment_date' => date('Y-m-d H:i:s')
            ];
            break;
            
        default:
            throw new Exception('Invalid payment method');
    }
    
    // If payment is successful, update order status and save payment details
    if ($payment_success) {
        // Update order status to 'paid'
        update_order_status($order_id, 'paid');
        
        // Save payment details
        save_payment($order_id, $payment_details);
        
        // Clear cart
        clear_cart();
        
        // Return success response
        echo json_encode(['success' => true, 'order_id' => $order_id]);
    } else {
        // Return error response
        echo json_encode(['success' => false, 'error' => 'Payment failed']);
    }
} catch (Exception $e) {
    // Log error
    error_log('Payment Error: ' . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
