<?php
$row = $_POST['row'];
$records = json_decode(file_get_contents('done.json'), true);

$records[$record['id']] = ['done' => $row['done'], 'observations' => $row['observations']];

file_put_contents('done.json', json_encode($records));
file_put_contents('done_' . time() . '.json', json_encode($records));
?>

