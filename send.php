<?php
$templates = [
    'mail-direct' => 'Conversation en direct',
    'mail-repondeur' => 'Répondeur',
    'mail-nna' => 'Numéro non-attribué',
    'mail-resiliation' => 'Résiliation'
];

function get_socio($chargebee_id, $socios)
{
    foreach($socios as $socio)
    {
        if($socio['chargebee_id'] == $chargebee_id)
            return $socio;
    }
    return null;
}

if (!empty($_POST))
{
    $socios = json_decode(file_get_contents('ciblage.json'), true);

    $authors = [
      'Katell' => 'katell@lemediatv.fr',
      'Thibault' => 'thibault@lemediatv.fr'
    ];

    $socio = get_socio($_POST['id'], $socios);

    $displayName = $socio['firstname'] . " " . $socio['lastname']

    $from = $_POST['from'];
    $template = $_POST['template'];

    $cmd = "cd emails && node send.js --production=production --displayName=\"$displayName\" --email=\"{$socio['email']}\" --updateCardUrl=\"{$socio['updateCardUrl']\" --author=\"{$from}\" --from=\"{$authors[$from]}\" --template=\"{$template}\"";
    exec($cmd);
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

<h2>Envoi e-mail</h2>

<form method="POST" action="send.php">
<input type="hidden" name="id" value="<?php echo $_GET['id'] ?>" />
<p>
<select name="author">
    <option value="Katell">Katell</option>
    <option value="Thibault">Thibault</option>
</select>
</p>
<p><select name="template">
<?php foreach($templates as $id => $template): ?>
    <option value="<?php echo $id ?>"><?php echo $template ?></option>
<?php endforeach; ?>
</select></p>
<p><input type="submit" value="Envoyer" /></p>
</form>
</html>
