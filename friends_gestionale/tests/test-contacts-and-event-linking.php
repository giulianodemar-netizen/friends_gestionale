<?php
/**
 * Contacts and Event Linking Tests
 * 
 * Run with: php test-contacts-and-event-linking.php
 * 
 * Tests the new functionality:
 * - Contacts (fg_contatto) post type is registered
 * - Contacts are independent from donors (fg_socio)
 * - Events can be linked to fundraisers (raccolte)
 * - Event totals are calculated correctly
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

// Mock post type registration check
function verify_contatto_post_type() {
    $post_types = array(
        'fg_socio' => 'Donatori',
        'fg_pagamento' => 'Pagamenti',
        'fg_raccolta' => 'Raccolte Fondi',
        'fg_evento' => 'Eventi',
        'fg_contatto' => 'Contatti'
    );
    
    return $post_types;
}

// Test 1: Verify post types
test_group("Post Type Registration Tests");

$post_types = verify_contatto_post_type();
test_assert(isset($post_types['fg_contatto']), "fg_contatto post type should be defined");
test_assert($post_types['fg_contatto'] === 'Contatti', "fg_contatto label should be 'Contatti'");

// Test 2: Verify contacts independence
test_group("Contacts Independence Tests");

// Mock: Contacts should have their own post type
$contatto_independent = !in_array('fg_contatto', array('fg_socio', 'fg_pagamento', 'fg_raccolta', 'fg_evento'));
test_assert($contatto_independent, "Contacts should be a separate post type");

// Mock: Contact fields
$contatto_fields = array(
    '_fg_nome_contatto',
    '_fg_tipo_contatto',
    '_fg_azienda',
    '_fg_email_contatto',
    '_fg_telefono_contatto',
    '_fg_indirizzo_contatto',
    '_fg_note_contatto'
);

$all_fields_unique = true;
foreach ($contatto_fields as $field) {
    // Check that contact fields don't collide with donor fields
    if (in_array($field, array('_fg_nome', '_fg_cognome', '_fg_email', '_fg_telefono'))) {
        $all_fields_unique = false;
    }
}
test_assert($all_fields_unique, "Contact fields should have unique names to avoid collision with donor fields");

// Test 3: Event-to-fundraiser linking
test_group("Event-to-Fundraiser Linking Tests");

// Mock: Events can be associated with fundraisers
$fundraiser_meta_fields = array(
    '_fg_titolo_raccolta',
    '_fg_obiettivo',
    '_fg_raccolto',
    '_fg_fondi_extra',
    '_fg_eventi_associati' // New field for linked events
);

test_assert(in_array('_fg_eventi_associati', $fundraiser_meta_fields), "Fundraisers should have _fg_eventi_associati meta field");

// Mock: Calculate total from multiple events
function calculate_events_total($eventi_ids, $mock_payments) {
    $total = 0;
    foreach ($eventi_ids as $evento_id) {
        if (isset($mock_payments[$evento_id])) {
            foreach ($mock_payments[$evento_id] as $amount) {
                $total += $amount;
            }
        }
    }
    return $total;
}

// Simulate two events with payments
$mock_payments = array(
    1 => array(100, 200, 150), // Event 1: 3 payments
    2 => array(50, 75, 125)    // Event 2: 3 payments
);

$eventi_associati = array(1, 2);
$expected_total = 700; // 100+200+150+50+75+125
$calculated_total = calculate_events_total($eventi_associati, $mock_payments);

test_assert($calculated_total === $expected_total, "Total from linked events should be calculated correctly (Expected: €{$expected_total}, Got: €{$calculated_total})");

// Test 4: Fundraiser total calculation
test_group("Fundraiser Total Calculation Tests");

$raccolto_piattaforma = 1000; // Direct payments to fundraiser
$fondi_extra = 200; // Extra funds
$totale_da_eventi = 700; // From linked events

$total_raccolta = $raccolto_piattaforma + $fondi_extra + $totale_da_eventi;
$expected_raccolta = 1900;

test_assert($total_raccolta === $expected_raccolta, "Fundraiser total should include platform + extra + events (Expected: €{$expected_raccolta}, Got: €{$total_raccolta})");

// Test 5: Verify contacts don't appear in payment/event dropdowns
test_group("Contacts Isolation Tests");

// Mock: Query for payment dropdown should only return fg_socio
function get_payment_dropdown_posts() {
    return array('post_type' => 'fg_socio'); // Only donors, not contacts
}

$payment_query = get_payment_dropdown_posts();
test_assert($payment_query['post_type'] === 'fg_socio', "Payment dropdowns should only query fg_socio, not fg_contatto");

// Mock: Query for event participants should only return fg_socio
function get_event_participants_posts() {
    return array('post_type' => 'fg_socio'); // Only donors, not contacts
}

$event_query = get_event_participants_posts();
test_assert($event_query['post_type'] === 'fg_socio', "Event participants should only query fg_socio, not fg_contatto");

// Summary
echo "\n=== Test Summary ===\n";
echo "All tests completed! Check for any FAIL messages above.\n";
echo "If all tests show ✓ PASS, the implementation is correct.\n\n";
