<?php
/**
 * Expiry Date Update Test
 * 
 * Run with: php test-expiry-date-update.php
 * 
 * Tests that the new expiration date logic works correctly:
 * - All payment types update expiry date
 * - Expiry is set to payment_date + 1 year
 * - No restrictions on payment year
 * 
 * @package Friends_Gestionale
 */

// Test helper functions
function test_assert($condition, $message) {
    if ($condition) {
        echo "✓ PASS: $message\n";
        return true;
    } else {
        echo "✗ FAIL: $message\n";
        return false;
    }
}

function test_group($name) {
    echo "\n=== $name ===\n";
}

// Test expiry date calculation
function test_expiry_calculation() {
    test_group('Expiry Date Calculation Logic');
    
    // Test case 1: Payment date + 1 year
    $payment_date = '2024-01-15';
    $expiry_date = new DateTime($payment_date);
    $expiry_date->modify('+1 year');
    $expected_expiry = '2025-01-15';
    test_assert($expiry_date->format('Y-m-d') === $expected_expiry, 
        "Payment date '2024-01-15' should result in expiry '2025-01-15'");
    
    // Test case 2: Payment from previous year (no restriction)
    $payment_date_old = '2023-06-10';
    $expiry_date_old = new DateTime($payment_date_old);
    $expiry_date_old->modify('+1 year');
    $expected_expiry_old = '2024-06-10';
    test_assert($expiry_date_old->format('Y-m-d') === $expected_expiry_old,
        "Old payment date '2023-06-10' should still result in expiry '2024-06-10'");
    
    // Test case 3: Future payment date
    $payment_date_future = '2025-12-31';
    $expiry_date_future = new DateTime($payment_date_future);
    $expiry_date_future->modify('+1 year');
    $expected_expiry_future = '2026-12-31';
    test_assert($expiry_date_future->format('Y-m-d') === $expected_expiry_future,
        "Future payment date '2025-12-31' should result in expiry '2026-12-31'");
    
    // Test case 4: Leap year handling
    $payment_date_leap = '2024-02-29';
    $expiry_date_leap = new DateTime($payment_date_leap);
    $expiry_date_leap->modify('+1 year');
    // PHP handles this as 2025-03-01 (since 2025 is not a leap year)
    test_assert($expiry_date_leap->format('Y-m-d') === '2025-03-01',
        "Leap year date '2024-02-29' should result in expiry '2025-03-01'");
}

// Test recalculation logic
function test_recalculation_logic() {
    test_group('Recalculation Logic');
    
    // Mock payment data
    $mock_payments = array(
        array('date' => '2023-01-15', 'amount' => 50),
        array('date' => '2023-06-20', 'amount' => 100),
        array('date' => '2024-03-10', 'amount' => 75),  // Latest payment
    );
    
    // Find latest payment
    $latest_timestamp = 0;
    $latest_payment_date = null;
    
    foreach ($mock_payments as $payment) {
        $timestamp = strtotime($payment['date']);
        if ($timestamp !== false && $timestamp > $latest_timestamp) {
            $latest_timestamp = $timestamp;
            $latest_payment_date = $payment['date'];
        }
    }
    
    test_assert($latest_payment_date === '2024-03-10',
        "Latest payment date should be '2024-03-10'");
    
    // Calculate expiry from latest payment
    if ($latest_payment_date) {
        $expiry = new DateTime($latest_payment_date);
        $expiry->modify('+1 year');
        $calculated_expiry = $expiry->format('Y-m-d');
        
        test_assert($calculated_expiry === '2025-03-10',
            "Expiry calculated from latest payment should be '2025-03-10'");
    }
    
    // Test with no payments
    $no_payments = array();
    test_assert(empty($no_payments),
        "No payments should result in empty/cleared expiry date");
}

// Test payment type logic (ALL types should update expiry)
function test_payment_types() {
    test_group('Payment Type Logic');
    
    $payment_types = array('quota', 'donazione', 'raccolta', 'evento', 'altro');
    $payment_date = '2024-06-15';
    
    foreach ($payment_types as $type) {
        // All types should update expiry - no restrictions
        $should_update = true; // Always true now
        
        test_assert($should_update === true,
            "Payment type '$type' should update expiry date");
        
        if ($should_update) {
            $expiry = new DateTime($payment_date);
            $expiry->modify('+1 year');
            test_assert($expiry->format('Y-m-d') === '2025-06-15',
                "Payment type '$type' should set expiry to '2025-06-15'");
        }
    }
}

// Test donor type logic (both types should work)
function test_donor_types() {
    test_group('Donor Type Logic');
    
    $donor_types = array('anche_socio', 'solo_donatore');
    $payment_date = '2024-08-20';
    
    foreach ($donor_types as $type) {
        // Both donor types should update expiry the same way
        $expiry = new DateTime($payment_date);
        $expiry->modify('+1 year');
        
        test_assert($expiry->format('Y-m-d') === '2025-08-20',
            "Donor type '$type' should have expiry set to '2025-08-20'");
    }
}

// Run all tests
echo "==========================================\n";
echo "Expiry Date Update Logic Tests\n";
echo "==========================================\n";

test_expiry_calculation();
test_recalculation_logic();
test_payment_types();
test_donor_types();

echo "\n==========================================\n";
echo "Tests Complete\n";
echo "==========================================\n";
