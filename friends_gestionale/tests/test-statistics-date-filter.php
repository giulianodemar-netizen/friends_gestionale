<?php
/**
 * Statistics Date Filter Tests
 * 
 * Run with: php test-statistics-date-filter.php
 * 
 * Tests the date filter functionality for statistics:
 * - Date validation (format and range)
 * - Query filtering by date range
 * - Single date filtering (start or end only)
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

// Mock date validation function
function validate_date_filter($start_date, $end_date) {
    $errors = array();
    
    // Validate start date format
    if (!empty($start_date) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
        $errors[] = 'Formato data inizio non valido. Usa YYYY-MM-DD.';
    }
    
    // Validate end date format
    if (!empty($end_date) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
        $errors[] = 'Formato data fine non valido. Usa YYYY-MM-DD.';
    }
    
    // Validate date range
    if (!empty($start_date) && !empty($end_date) && $start_date > $end_date) {
        $errors[] = 'La data inizio deve essere precedente o uguale alla data fine.';
    }
    
    return array(
        'valid' => empty($errors),
        'errors' => $errors
    );
}

// Mock function to simulate filtering payments by date
function filter_payments_by_date($payments, $start_date, $end_date) {
    if (empty($start_date) && empty($end_date)) {
        return $payments;
    }
    
    return array_filter($payments, function($payment) use ($start_date, $end_date) {
        $payment_date = $payment['date'];
        
        if (!empty($start_date) && $payment_date < $start_date) {
            return false;
        }
        
        if (!empty($end_date) && $payment_date > $end_date) {
            return false;
        }
        
        return true;
    });
}

// Run tests
echo "=== Friends Gestionale - Statistics Date Filter Tests ===\n";

test_group("Test 1: Valid Date Format");
$result = validate_date_filter('2024-01-01', '2024-12-31');
test_assert($result['valid'] === true, 'Valid date range should pass');
test_assert(count($result['errors']) === 0, 'No errors for valid dates');

test_group("Test 2: Invalid Date Format - Start Date");
$result = validate_date_filter('01-01-2024', '2024-12-31');
test_assert($result['valid'] === false, 'Invalid start date format should fail');
test_assert(count($result['errors']) === 1, 'Should have one error');
test_assert(strpos($result['errors'][0], 'inizio') !== false, 'Error should mention start date');

test_group("Test 3: Invalid Date Format - End Date");
$result = validate_date_filter('2024-01-01', '31/12/2024');
test_assert($result['valid'] === false, 'Invalid end date format should fail');
test_assert(count($result['errors']) === 1, 'Should have one error');
test_assert(strpos($result['errors'][0], 'fine') !== false, 'Error should mention end date');

test_group("Test 4: Invalid Date Range - Start After End");
$result = validate_date_filter('2024-12-31', '2024-01-01');
test_assert($result['valid'] === false, 'Start date after end date should fail');
test_assert(count($result['errors']) === 1, 'Should have one error');
test_assert(strpos($result['errors'][0], 'precedente') !== false, 'Error should mention date order');

test_group("Test 5: Valid Date Range - Same Date");
$result = validate_date_filter('2024-06-15', '2024-06-15');
test_assert($result['valid'] === true, 'Same start and end date should be valid');
test_assert(count($result['errors']) === 0, 'No errors for same date');

test_group("Test 6: Only Start Date");
$result = validate_date_filter('2024-01-01', '');
test_assert($result['valid'] === true, 'Only start date should be valid');
test_assert(count($result['errors']) === 0, 'No errors for start date only');

test_group("Test 7: Only End Date");
$result = validate_date_filter('', '2024-12-31');
test_assert($result['valid'] === true, 'Only end date should be valid');
test_assert(count($result['errors']) === 0, 'No errors for end date only');

test_group("Test 8: Empty Dates");
$result = validate_date_filter('', '');
test_assert($result['valid'] === true, 'Empty dates should be valid (no filter)');
test_assert(count($result['errors']) === 0, 'No errors for empty dates');

test_group("Test 9: Payment Filtering - Date Range");
$payments = array(
    array('date' => '2024-01-15', 'amount' => 100),
    array('date' => '2024-02-20', 'amount' => 200),
    array('date' => '2024-03-10', 'amount' => 150),
    array('date' => '2024-04-05', 'amount' => 300),
);
$filtered = filter_payments_by_date($payments, '2024-02-01', '2024-03-31');
test_assert(count($filtered) === 2, 'Should filter to 2 payments in range');
test_assert(isset($filtered[1]) && $filtered[1]['date'] === '2024-02-20', 'Should include payment in February');
test_assert(isset($filtered[2]) && $filtered[2]['date'] === '2024-03-10', 'Should include payment in March');

test_group("Test 10: Payment Filtering - Start Date Only");
$filtered = filter_payments_by_date($payments, '2024-03-01', '');
test_assert(count($filtered) === 2, 'Should filter payments from March onwards');
test_assert(isset($filtered[2]) && $filtered[2]['amount'] === 150, 'Should include March payment');
test_assert(isset($filtered[3]) && $filtered[3]['amount'] === 300, 'Should include April payment');

test_group("Test 11: Payment Filtering - End Date Only");
$filtered = filter_payments_by_date($payments, '', '2024-02-28');
test_assert(count($filtered) === 2, 'Should filter payments until February');
test_assert(isset($filtered[0]) && $filtered[0]['date'] === '2024-01-15', 'Should include January payment');
test_assert(isset($filtered[1]) && $filtered[1]['date'] === '2024-02-20', 'Should include February payment');

test_group("Test 12: Payment Filtering - No Filter");
$filtered = filter_payments_by_date($payments, '', '');
test_assert(count($filtered) === 4, 'Should return all payments when no filter is applied');

echo "\n=== All Tests Completed ===\n";
echo "\nNote: These tests verify the date filter validation and query logic.\n";
echo "To verify the actual functionality in WordPress:\n";
echo "1. Navigate to Friends Gestionale → Statistiche\n";
echo "2. Set date range filters and click 'Applica Filtro'\n";
echo "3. Verify that charts and statistics update based on the date range\n";
echo "4. Try invalid dates to see validation errors\n";
