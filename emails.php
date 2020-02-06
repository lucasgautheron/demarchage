<?php
set_time_limit(4000); 

// Connect to gmail
$hostname = '{imap.ionos.fr:993/ssl/novalidate-cert}INBOX';
$password = file_get_contents('email_pwd');
$email = $_GET['email'];

$users = [
    ['question@lemediatv.fr', file_get_contents('questions_pwd')],
    ['bonjour@lemediatv.fr', file_get_contents('bonjour_pwd')]
];

function get_emails($from, $hostname, $username, $password)
{
    $inbox = imap_open($hostname, $username, $password) or die('Cannot connect: ' . 
    imap_last_error());

    $emails = imap_search($inbox,'FROM "'.$from.'"', SE_FREE, "UTF-8");
    $emails = array_reverse($emails);
    $output = '';

    $n = 0;

    $collection = [];
    foreach($emails as $mail) {
        $headerInfo = imap_headerinfo($inbox,$mail);
        $subject = $headerInfo->subject;
        $body = imap_fetchbody ($inbox, $mail, 1);
        $collection[] = ['header' => $headerInfo, 'subject' => $subject, 'body' => $body];
    }

    return $collection;
}

$emails = [];
foreach($users as $user)
{
    $emails[$user[0]] = get_emails($email, $hostname, $user[0], $user[1]);   
}

print_r($emails);
