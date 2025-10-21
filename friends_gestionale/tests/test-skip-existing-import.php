<?php
/**
 * Skip Existing Records Import Tests
 * 
 * Run with: php test-skip-existing-import.php
 * 
 * Tests the skip_existing flag functionality for the import feature:
 * - Records with existing email are skipped when flag is enabled
 * - Records are not modified when skipped
 * - Records are properly created/updated when flag is disabled
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

// Mock validation function (extracted from class-import.php logic)
function validate_donor_row_with_skip($row_data, $existing_email = false, $update_existing = true, $skip_existing = false) {
    $errors = array();
    $warnings = array();
    
    $email = isset($row_data['email']) ? trim($row_data['email']) : '';
    $nome = isset($row_data['nome']) ? trim($row_data['nome']) : '';
    $cognome = isset($row_data['cognome']) ? trim($row_data['cognome']) : '';
    
    // Basic validation
    if (empty($nome)) {
        $errors[] = 'Nome richiesto';
    }
    if (empty($cognome)) {
        $errors[] = 'Cognome richiesto';
    }
    
    // Determine status based on existing email and flags
    $status = 'create';
    $action_label = 'Nuovo';
    
    if ($existing_email) {
        if ($skip_existing) {
            // Skip existing records when flag is enabled
            $status = 'skip';
            $action_label = 'Salta';
            $warnings[] = 'Email già esistente - il record verrà saltato (non modificato)';
        } elseif ($update_existing) {
            $status = 'update';
            $action_label = 'Aggiorna';
            $warnings[] = 'Email già esistente - il record verrà aggiornato';
        } else {
            $status = 'create';
            $action_label = 'Crea nuovo';
            $warnings[] = 'Email già esistente - verrà creato un nuovo record (duplicato)';
        }
    }
    
    if (!empty($errors)) {
        $status = 'error';
        $action_label = 'Errore';
    }
    
    return array(
        'status' => $status,
        'action_label' => $action_label,
        'errors' => $errors,
        'warnings' => $warnings
    );
}

// Run tests
echo "=== Friends Gestionale - Skip Existing Import Tests ===\n";

test_group("Test 1: Skip Existing Flag - Record with Existing Email");
$result = validate_donor_row_with_skip(
    array('nome' => 'Mario', 'cognome' => 'Rossi', 'email' => 'mario@example.com'),
    true,  // existing_email
    true,  // update_existing
    true   // skip_existing
);
test_assert($result['status'] === 'skip', 'Status should be "skip" when skip_existing is enabled');
test_assert($result['action_label'] === 'Salta', 'Action label should be "Salta"');
test_assert(count($result['warnings']) === 1, 'Should have 1 warning');
test_assert(strpos($result['warnings'][0], 'saltato') !== false, 'Warning should mention "saltato"');

test_group("Test 2: Skip Existing Flag Disabled - Update Behavior");
$result = validate_donor_row_with_skip(
    array('nome' => 'Mario', 'cognome' => 'Rossi', 'email' => 'mario@example.com'),
    true,  // existing_email
    true,  // update_existing
    false  // skip_existing
);
test_assert($result['status'] === 'update', 'Status should be "update" when skip_existing is disabled and update_existing is enabled');
test_assert($result['action_label'] === 'Aggiorna', 'Action label should be "Aggiorna"');

test_group("Test 3: Skip Existing Flag Disabled - Create Duplicate Behavior");
$result = validate_donor_row_with_skip(
    array('nome' => 'Mario', 'cognome' => 'Rossi', 'email' => 'mario@example.com'),
    true,   // existing_email
    false,  // update_existing
    false   // skip_existing
);
test_assert($result['status'] === 'create', 'Status should be "create" (duplicate) when both flags are disabled');
test_assert($result['action_label'] === 'Crea nuovo', 'Action label should be "Crea nuovo"');
test_assert(strpos($result['warnings'][0], 'duplicato') !== false, 'Warning should mention "duplicato"');

test_group("Test 4: New Record - Skip Flag Has No Effect");
$result = validate_donor_row_with_skip(
    array('nome' => 'Luigi', 'cognome' => 'Verdi', 'email' => 'luigi@example.com'),
    false, // existing_email
    true,  // update_existing
    true   // skip_existing
);
test_assert($result['status'] === 'create', 'Status should be "create" for new email regardless of skip flag');
test_assert($result['action_label'] === 'Nuovo', 'Action label should be "Nuovo"');
test_assert(count($result['warnings']) === 0, 'Should have no warnings for new record');

test_group("Test 5: Skip Flag with Invalid Data");
$result = validate_donor_row_with_skip(
    array('nome' => '', 'cognome' => 'Rossi', 'email' => 'mario@example.com'),
    true,  // existing_email
    true,  // update_existing
    true   // skip_existing
);
test_assert($result['status'] === 'error', 'Status should be "error" even with skip flag when validation fails');
test_assert(count($result['errors']) === 1, 'Should have validation error');

test_group("Test 6: Skip Has Priority Over Update");
$result = validate_donor_row_with_skip(
    array('nome' => 'Mario', 'cognome' => 'Rossi', 'email' => 'mario@example.com'),
    true,  // existing_email
    true,  // update_existing (this should be ignored)
    true   // skip_existing (this should take priority)
);
test_assert($result['status'] === 'skip', 'Skip should take priority over update when both flags are enabled');
test_assert(strpos($result['warnings'][0], 'saltato') !== false, 'Should show skip warning, not update warning');

echo "\n=== All Tests Completed ===\n";
