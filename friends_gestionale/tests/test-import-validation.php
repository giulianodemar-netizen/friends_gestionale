<?php
/**
 * Import Validation Tests
 * 
 * Run with: php test-import-validation.php
 * 
 * Tests the validation logic for the import functionality:
 * - ragione_sociale vs nome/cognome requirement
 * - email validation
 * - data_iscrizione default for soci
 * - ruolo normalization
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
function validate_donor_row($row_data) {
    $errors = array();
    $warnings = array();
    
    $ragione_sociale = isset($row_data['ragione_sociale']) ? trim($row_data['ragione_sociale']) : '';
    $nome = isset($row_data['nome']) ? trim($row_data['nome']) : '';
    $cognome = isset($row_data['cognome']) ? trim($row_data['cognome']) : '';
    $email = isset($row_data['email']) ? trim($row_data['email']) : '';
    $tipo_donatore = isset($row_data['ruolo']) ? trim($row_data['ruolo']) : 'anche_socio';
    $data_iscrizione = isset($row_data['data_iscrizione']) ? trim($row_data['data_iscrizione']) : '';
    
    // Normalize ruolo value
    if (in_array(strtolower($tipo_donatore), array('socio', 'membro', 'member', 'anche_socio'))) {
        $tipo_donatore = 'anche_socio';
    } elseif (in_array(strtolower($tipo_donatore), array('donatore', 'donor', 'solo_donatore'))) {
        $tipo_donatore = 'solo_donatore';
    }
    
    // Rule: Either ragione_sociale OR (nome AND cognome) required
    if (empty($ragione_sociale)) {
        if (empty($nome) && empty($cognome)) {
            $errors[] = 'Richiesto almeno Nome e Cognome o Ragione Sociale';
        } elseif (empty($nome)) {
            $errors[] = 'Nome richiesto';
        } elseif (empty($cognome)) {
            $errors[] = 'Cognome richiesto';
        }
    }
    
    // Email validation
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email non valida';
    }
    
    // Default data_iscrizione for soci
    if ($tipo_donatore === 'anche_socio') {
        if (empty($data_iscrizione)) {
            $data_iscrizione = date('Y-m-d');
            $warnings[] = 'Data iscrizione impostata a oggi';
        }
    }
    
    return array(
        'valid' => empty($errors),
        'errors' => $errors,
        'warnings' => $warnings,
        'tipo_donatore' => $tipo_donatore,
        'data_iscrizione' => $data_iscrizione
    );
}

// Run tests
echo "=== Friends Gestionale - Import Validation Tests ===\n";

test_group("Test 1: Nome e Cognome Required for Privati");
$result = validate_donor_row(array(
    'nome' => 'Mario',
    'cognome' => 'Rossi',
    'email' => 'mario.rossi@example.com'
));
test_assert($result['valid'], "Valid row with nome and cognome");
test_assert(count($result['errors']) === 0, "No errors for valid privato");

$result = validate_donor_row(array(
    'nome' => 'Mario',
    'cognome' => '',
    'email' => 'mario.rossi@example.com'
));
test_assert(!$result['valid'], "Invalid row with missing cognome");
test_assert(in_array('Cognome richiesto', $result['errors']), "Error message for missing cognome");

$result = validate_donor_row(array(
    'nome' => '',
    'cognome' => 'Rossi',
    'email' => 'mario.rossi@example.com'
));
test_assert(!$result['valid'], "Invalid row with missing nome");
test_assert(in_array('Nome richiesto', $result['errors']), "Error message for missing nome");

$result = validate_donor_row(array(
    'nome' => '',
    'cognome' => '',
    'email' => 'mario.rossi@example.com'
));
test_assert(!$result['valid'], "Invalid row with both nome and cognome missing");
test_assert(in_array('Richiesto almeno Nome e Cognome o Ragione Sociale', $result['errors']), "Error message for both missing");

test_group("Test 2: Ragione Sociale for Società");
$result = validate_donor_row(array(
    'ragione_sociale' => 'Azienda SRL',
    'nome' => '',
    'cognome' => '',
    'email' => 'info@azienda.com'
));
test_assert($result['valid'], "Valid row with ragione_sociale only");
test_assert(count($result['errors']) === 0, "No errors when ragione_sociale is present");

$result = validate_donor_row(array(
    'ragione_sociale' => 'Azienda SRL',
    'nome' => 'Mario',
    'cognome' => '',
    'email' => 'info@azienda.com'
));
test_assert($result['valid'], "Valid row with ragione_sociale and nome (referente)");

test_group("Test 3: Email Validation");
$result = validate_donor_row(array(
    'nome' => 'Mario',
    'cognome' => 'Rossi',
    'email' => 'mario.rossi@example.com'
));
test_assert($result['valid'], "Valid email accepted");

$result = validate_donor_row(array(
    'nome' => 'Mario',
    'cognome' => 'Rossi',
    'email' => 'invalid-email'
));
test_assert(!$result['valid'], "Invalid email rejected");
test_assert(in_array('Email non valida', $result['errors']), "Error message for invalid email");

$result = validate_donor_row(array(
    'nome' => 'Mario',
    'cognome' => 'Rossi',
    'email' => ''
));
test_assert($result['valid'], "Empty email accepted (optional field)");

test_group("Test 4: Data Iscrizione Default for Soci");
$result = validate_donor_row(array(
    'nome' => 'Mario',
    'cognome' => 'Rossi',
    'ruolo' => 'socio',
    'data_iscrizione' => ''
));
test_assert($result['valid'], "Valid socio without data_iscrizione");
test_assert(!empty($result['data_iscrizione']), "Data iscrizione defaulted to today");
test_assert($result['data_iscrizione'] === date('Y-m-d'), "Data iscrizione is today's date");
test_assert(in_array('Data iscrizione impostata a oggi', $result['warnings']), "Warning message for default date");

$result = validate_donor_row(array(
    'nome' => 'Mario',
    'cognome' => 'Rossi',
    'ruolo' => 'socio',
    'data_iscrizione' => '2024-01-15'
));
test_assert($result['valid'], "Valid socio with explicit data_iscrizione");
test_assert($result['data_iscrizione'] === '2024-01-15', "Data iscrizione preserved");
test_assert(!in_array('Data iscrizione impostata a oggi', $result['warnings']), "No warning when date is provided");

$result = validate_donor_row(array(
    'nome' => 'Luigi',
    'cognome' => 'Verdi',
    'ruolo' => 'donatore',
    'data_iscrizione' => ''
));
test_assert($result['valid'], "Valid donatore without data_iscrizione");
test_assert(empty($result['data_iscrizione']), "No default date for donatori");

test_group("Test 5: Ruolo Normalization");
$test_cases = array(
    'socio' => 'anche_socio',
    'Socio' => 'anche_socio',
    'SOCIO' => 'anche_socio',
    'membro' => 'anche_socio',
    'member' => 'anche_socio',
    'anche_socio' => 'anche_socio',
    'donatore' => 'solo_donatore',
    'Donatore' => 'solo_donatore',
    'DONATORE' => 'solo_donatore',
    'donor' => 'solo_donatore',
    'solo_donatore' => 'solo_donatore',
);

foreach ($test_cases as $input => $expected) {
    $result = validate_donor_row(array(
        'nome' => 'Test',
        'cognome' => 'User',
        'ruolo' => $input
    ));
    test_assert($result['tipo_donatore'] === $expected, "Ruolo '$input' normalized to '$expected'");
}

test_group("Test 6: Combined Validation Scenarios");
// Scenario 1: Società socio without data_iscrizione
$result = validate_donor_row(array(
    'ragione_sociale' => 'Tech Company SRL',
    'email' => 'info@techcompany.com',
    'ruolo' => 'anche_socio',
    'data_iscrizione' => ''
));
test_assert($result['valid'], "Società socio without date is valid");
test_assert($result['data_iscrizione'] === date('Y-m-d'), "Data iscrizione defaulted for società socio");

// Scenario 2: Invalid email with missing names
$result = validate_donor_row(array(
    'nome' => '',
    'cognome' => '',
    'email' => 'not-an-email',
    'ruolo' => 'donatore'
));
test_assert(!$result['valid'], "Multiple errors detected");
test_assert(count($result['errors']) === 2, "Both name and email errors present");

// Scenario 3: Donatore with all fields valid
$result = validate_donor_row(array(
    'nome' => 'Paolo',
    'cognome' => 'Bianchi',
    'email' => 'paolo.bianchi@example.com',
    'telefono' => '3331234567',
    'ruolo' => 'donatore'
));
test_assert($result['valid'], "Complete valid donatore record");
test_assert($result['tipo_donatore'] === 'solo_donatore', "Ruolo correctly normalized to solo_donatore");

echo "\n=== All Tests Completed ===\n";
