<?php
function is_done($chargebee_id)
{
    global $records;

    if (@array_key_exists($chargebee_id, $records))
    {
        return $records[$chargebee_id]['done'] == "true";
    }
    return false;
    
}

function get_field($chargebee_id, $field)
{
    global $records;

    if (@array_key_exists($chargebee_id, $records))
    {
        return $records[$chargebee_id][$field];
    }
    return false;
}

function observations($chargebee_id)
{
    global $records;
    if (@array_key_exists($chargebee_id, $records))
    {
        return $records[$chargebee_id]['observations'];
    }
    return '';
}

function format_phone($number)
{
    $number = preg_replace("#[^0-9]#", '', $number);
    $number = '0' . substr($number, -9);
    return join(' ', str_split($number, 2));
}

$socios = json_decode(file_get_contents('ciblage.json'), true);
$records = json_decode(file_get_contents('done.json'), true);

$output = [];

foreach($socios as $socio)
{
    $entry = [];
    $entry['id'] = $socio['chargebee_id'];
//    $entry['email'] = $socio['email'];
    $entry['email'] = '<a href="emails.php?email=' . $socio['email'] . '" target="_blank">' . $socio['email'] . '</a>';
    $entry['nom'] = $socio['firstname'] . " " . $socio['lastname'];
    $entry['facturation'] = $socio['billing_period_unit'];
    $entry['echeance'] = date('Y-m-d', $socio['next_billing_at']/1000);
    $entry['expirationcb'] = date('Y-m', $socio['card_expiry']/1000);
    $entry['montant'] = $socio['amount'];
    $entry['telephone'] = format_phone($socio['phone']);
    $url = str_replace('https://lemediatv.fr', 'https://www.lemediatv.fr', $socio['updateCardUrl']);
    $entry['url'] = '<a href="' . $url . '" target="_blank">URL</a>';
    $entry['done'] = get_field($socio['chargebee_id'], 'done');
    $entry['observations'] = get_field($socio['chargebee_id'], 'observations');
    $entry['traitement'] = get_field($socio['chargebee_id'], 'traitement');
    $entry['datetraitement'] = get_field($socio['chargebee_id'], 'datetraitement');
    $output[] = $entry;
}

echo json_encode($output);
?>
