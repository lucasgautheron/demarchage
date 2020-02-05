<?php
$records = [];
foreach($_POST['data'] as $record)
{
    $records[$record['id']] = ['done' => $record['done'], 'observations' => $record['observations']];
}
file_put_contents('done.json', json_encode($records));
file_put_contents('done_' . time() . '.json', json_encode($records));
?>

