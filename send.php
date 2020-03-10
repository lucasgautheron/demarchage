<?php
$templates = [
    'mail-direct' => ['Conversation en direct', 'Renouvellement de votre cotisation au Média TV'],
    'mail-repondeur' => ['Répondeur', 'Renouvellement de votre adhésion au Média TV'],
    'mail-nna' => ['Numéro non-attribué', 'Renouvellement de votre adhésion au Média TV'],
    'mail-resiliation' => ['Résiliation', 'Résiliation de votre adhsion au Média TV']
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

$send = false;
if (!empty($_POST['id']))
{
    $send = true;
    $socios = json_decode(file_get_contents('ciblage.json'), true);

    $authors = [
      'Katell' => 'katell@lemediatv.fr',
      'Thibault' => 'thibault@lemediatv.fr'
    ];

    $socio = get_socio($_POST['id'], $socios);

    $displayName = $socio['firstname'] . " " . $socio['lastname'];

    $author = $_POST['author'];
    $author_email = $authors[$author];
    $template = $_POST['template'];
    $subject = $templates[$template][1];

    $cmd = "cd emails && node send.js --production=production --displayName=\"$displayName\" --email=\"{$socio['email']}\" --updateCardUrl=\"{$socio['updateCardUrl']}\" --author=\"$author\" --from=\"{$author_email}\" --template=\"{$template}\" --subject=\"$subject\"";
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

<?php if ($send) : ?>
<p>E-mail envoyé !</p>
<?php else : ?>
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
    <option value="<?php echo $id ?>"><?php echo $template[0] ?></option>
<?php endforeach; ?>
</select></p>
<p><input type="submit" value="Envoyer" /></p>
</form>
<?php endif; ?>
</div>
</html>
