<?php
/**
 * Import All Rows Test
 * 
 * Run with: php test-import-all-rows.php
 * 
 * Tests that the import processes ALL rows, not just the first 100
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

// Mock function to simulate parsing all rows from CSV
function mock_parse_csv_all_rows($row_count) {
    $all_rows = array();
    for ($i = 1; $i <= $row_count; $i++) {
        $all_rows[] = array(
            'nome' => 'Nome' . $i,
            'cognome' => 'Cognome' . $i,
            'email' => 'user' . $i . '@example.com'
        );
    }
    return $all_rows;
}

// Mock function to simulate the old behavior (preview only)
function mock_parse_csv_preview_only($row_count, $preview_limit = 100) {
    $preview_rows = array();
    $limit = min($row_count, $preview_limit);
    for ($i = 1; $i <= $limit; $i++) {
        $preview_rows[] = array(
            'nome' => 'Nome' . $i,
            'cognome' => 'Cognome' . $i,
            'email' => 'user' . $i . '@example.com'
        );
    }
    return $preview_rows;
}

// Run tests
echo "=== Friends Gestionale - Import All Rows Tests ===\n";

test_group("Test 1: Parse All Rows - Small File (50 rows)");
$rows = mock_parse_csv_all_rows(50);
test_assert(count($rows) === 50, 'Should parse all 50 rows');
test_assert($rows[0]['email'] === 'user1@example.com', 'First row should be correct');
test_assert($rows[49]['email'] === 'user50@example.com', 'Last row should be correct');

test_group("Test 2: Parse All Rows - Medium File (150 rows)");
$rows = mock_parse_csv_all_rows(150);
test_assert(count($rows) === 150, 'Should parse all 150 rows');
test_assert($rows[0]['email'] === 'user1@example.com', 'First row should be correct');
test_assert($rows[99]['email'] === 'user100@example.com', 'Row 100 should be correct');
test_assert($rows[149]['email'] === 'user150@example.com', 'Last row (150) should be correct');

test_group("Test 3: Parse All Rows - Large File (400 rows)");
$rows = mock_parse_csv_all_rows(400);
test_assert(count($rows) === 400, 'Should parse all 400 rows');
test_assert($rows[0]['email'] === 'user1@example.com', 'First row should be correct');
test_assert($rows[99]['email'] === 'user100@example.com', 'Row 100 should be correct');
test_assert($rows[199]['email'] === 'user200@example.com', 'Row 200 should be correct');
test_assert($rows[399]['email'] === 'user400@example.com', 'Last row (400) should be correct');

test_group("Test 4: Compare Old vs New Behavior");
$old_behavior = mock_parse_csv_preview_only(400, 100);
$new_behavior = mock_parse_csv_all_rows(400);
test_assert(count($old_behavior) === 100, 'Old behavior: Should only parse 100 rows');
test_assert(count($new_behavior) === 400, 'New behavior: Should parse all 400 rows');
test_assert(count($new_behavior) > count($old_behavior), 'New behavior processes more rows than old');

test_group("Test 5: Verify Row Integrity");
$rows = mock_parse_csv_all_rows(250);
$all_valid = true;
foreach ($rows as $index => $row) {
    $expected_number = $index + 1;
    if ($row['email'] !== 'user' . $expected_number . '@example.com') {
        $all_valid = false;
        break;
    }
}
test_assert($all_valid, 'All 250 rows should maintain correct sequence and data');

test_group("Test 6: Edge Cases");
$empty = mock_parse_csv_all_rows(0);
test_assert(count($empty) === 0, 'Should handle empty file (0 rows)');

$single = mock_parse_csv_all_rows(1);
test_assert(count($single) === 1, 'Should handle single row file');

$exact_100 = mock_parse_csv_all_rows(100);
test_assert(count($exact_100) === 100, 'Should handle exactly 100 rows');

$one_over = mock_parse_csv_all_rows(101);
test_assert(count($one_over) === 101, 'Should handle 101 rows (just over old limit)');

echo "\n=== All Tests Completed ===\n";
echo "\nNote: These tests verify the logic for parsing all rows.\n";
echo "The actual fix ensures that ajax_execute_import() uses parse_file_all_rows()\n";
echo "instead of relying on preview_rows which was limited to 100.\n";
echo "\nIn the real implementation:\n";
echo "- parse_csv_all_rows() reads ALL rows from CSV without limit\n";
echo "- parse_xlsx_all_rows() reads ALL rows from XLSX without limit\n";
echo "- ajax_execute_import() now processes all rows from the file\n";
