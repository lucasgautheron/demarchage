<?php
set_time_limit(4000); 

// Connect to gmail
$hostname = '{imap.ionos.fr:993/ssl/novalidate-cert}INBOX';
$from = $_GET['email'];

$users = [
    ['question@lemediatv.fr', trim(file_get_contents('questions_pwd'))],
    ['bonjour@lemediatv.fr', trim(file_get_contents('bonjour_pwd'))]
];

function get_emails($from, $hostname, $username, $password)
{
    $inbox = imap_open($hostname, $username, $password) or die('Cannot connect: ' . 
    imap_last_error());

    $emails = imap_search($inbox,'FROM "'.$from.'"', SE_FREE, "UTF-8");

    if (!$emails) {
        return [];
    }

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

function utf8ize($d) {
    if (is_array($d)) 
        foreach ($d as $k => $v) 
            $d[$k] = utf8ize($v);

     else if(is_object($d))
        foreach ($d as $k => $v) 
            $d->$k = utf8ize($v);

     else 
        return utf8_encode($d);

    return $d;
}

function get_mailjet_sent($email)
{
    static $MJ_CREDENTIALS = null;

    if (!$MJ_CREDENTIALS) {
        $MJ_CREDENTIALS = json_decode(file_get_contents('emails/mailjet.json'), true);
        $MJ_APIKEY_PUBLIC = $MJ_CREDENTIALS['USER'];
        $MJ_APIKEY_PRIVATE = $MJ_CREDENTIALS['PASS'];
    }

    $headers = [
       "Content-Type: application/json"
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://api.mailjet.com/v3/REST/contact/" . urlencode($email));
    curl_setopt($ch, CURLOPT_USERPWD, "{$MJ_APIKEY_PUBLIC}:{$MJ_APIKEY_PRIVATE}");
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $output = curl_exec($ch);
    $data = json_decode($output, true)['Data'];
    $contact_id = $data['ID'];

    $params = [
      'Contact' => $contact_id,
      'ShowSubject' => 1,
      'Sort' => 'ToTS+DESC',
      'Limit' => 1000
    ];

    curl_setopt($ch, CURLOPT_URL, "https://api.mailjet.com/v3/REST/message/?" . http_build_query($params));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_USERPWD, "{$MJ_APIKEY_PUBLIC}:{$MJ_APIKEY_PRIVATE}");
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $output = curl_exec($ch);
    print_r(json_decode($output, true));
    return json_decode($output, true)['Data'];
}

$mailjet = get_mailjet_sent($from);

$emails = [];
foreach($users as $user)
{
    $tmp = get_emails($from, $hostname, $user[0], $user[1]);
    foreach($tmp as $email)
    {
        $data = [];
        $data['from'] = $email['header']->fromaddress;
        $data['to'] = $email['header']->toaddress;
        $data['date'] = $email['header']->date;
        $data['title'] = quoted_printable_decode($email['subject']);
        $data['message'] = quoted_printable_decode($email['body']);
        $emails[] = $data;
    }
}

?>

<!DOCTYPE html>
<html lang="fr" >

<head>

  <meta charset="UTF-8">
  
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css'>
<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.10.0/bootstrap-table.min.css'>
<link rel='stylesheet' href='https://rawgit.com/vitalets/x-editable/master/dist/bootstrap3-editable/css/bootstrap-editable.css'>
  
<style>
.container {
	width: 1024px;
	padding: 2em;
}

.bold-blue {
	font-weight: bold;
	color: #0277BD;
}

td,th { font-size: 75%; }
</style>

  <script>
  window.console = window.console || function(t) {};
</script>

  
  
  <script>
  if (document.location.search.match(/type=embed/gi)) {
    window.parent.postMessage("resize", "*");
  }
</script>


</head>

<body translate="no" >
  <div class="container">

<h2>E-mails reçus de <?php echo htmlspecialchars($from); ?></h2>

<div id="toolbar">
		<select class="form-control">
				<option value="">Export simple</option>
				<option value="all">Exporter tout</option>
				<option value="selected">Exporter la sélection</option>
		</select>
</div>

<table id="table">
</table>

<h2>E-mails transactionnels vers <?php echo htmlspecialchars($from); ?></h2>

<table id="table_transac">
</table>

</div>
    <script src="https://static.codepen.io/assets/common/stopExecutionOnTimeout-db44b196776521ea816683afab021f757616c80860d31da6232dedb8d7cc4862.js"></script>

  <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.10.0/bootstrap-table.js'></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.9.1/extensions/editable/bootstrap-table-editable.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.9.1/extensions/export/bootstrap-table-export.js'></script>
<script src='https://rawgit.com/hhurz/tableExport.jquery.plugin/master/tableExport.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.9.1/extensions/filter-control/bootstrap-table-filter-control.js'></script>
  
      <script id="rendered-js" >
var $table = $('#table');

var $table_transac  = $('#table_transac');

$(function() {
    $table.bootstrapTable({
      idField: 'date',
      toggle: 'table',
      filerControl: true,
      showExport: true,
      toolbar: "#toolbar",
      pagination: true,
      pageSize: 100,
      columns: [{
        field: 'state',
        title: '',
        checkbox: true
      }, {
        field: 'from',
        title: 'Expéditeur',
        visible: false,
        sortable: false
      }, {
        field: 'to',
        title: 'Destinataire',
        sortable: false,
        editable: false,
        searchable: true
      }, {
        field: 'date',
        title: 'Date',
        sortable: false,
        editable: false
      }, {
        field: 'title',
        title: 'Titre',
        sortable: false,
        editable: false,
        searchable: true
      }, {
        field: 'message',
        title: 'Message',
        sortable: false,
        editable: false,
        searchable: true
      }
      ],
      data: <?php echo json_encode($emails) ?>
    })
  })

$table_transac.bootstrapTable({
      idField: 'date',
      toggle: 'table',
      filerControl: true,
      pagination: true,
      pageSize: 100,
      columns: [{
        field: 'state',
        title: '',
        checkbox: true
      }, {
        field: 'Status',
        title: 'État',
        visible: true,
        sortable: false
      }, {
        field: 'SenderID',
        title: 'Expéditeur',
        visible: false,
        sortable: false
      }, {
        field: 'ArrivedAt',
        title: 'Date',
        sortable: false,
        editable: false
      }, {
        field: 'Subject',
        title: 'Titre',
        sortable: false,
        editable: false,
        searchable: true
      }
      ],
      data: <?php echo json_encode($mailjet) ?>
    })
  })

$(function () {
  $('#toolbar').find('select').change(function () {
    $table.bootstrapTable('refreshOptions', {
      exportDataType: $(this).val() });

  });
});
    </script>

<?php echo json_last_error(); ?>
</body>

</html>
