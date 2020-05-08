<?php
$templates = json_decode(file_get_contents('emails/templates.json'), true);

$authors = [
  'katell' => 'katell.gouello@lemediatv.fr',
  'thibault' => 'thibault@lemediatv.fr',
  'lucas' => 'lucas.gautheron@lemediatv.fr'
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

$socios = json_decode(file_get_contents('ciblage.json'), true);
$id = !empty($_GET['id']) ? $_GET['id'] : $_POST['id'];
$socio = get_socio($id, $socios);
$displayName = $socio['firstname'] . " " . $socio['lastname'];

$send = false;
if (!empty($_POST['id']))
{
    $send = true;

    $author = $_POST['author'];
    $author_email = $authors[$author];
    $template = $_POST['template'];
    $subject = $templates[$template]['subject'];

    if (is_array($subject)) {
        $subject = $templates[$template]['subject'][array_rand($templates[$template]['subject'])];
    }

    $attachment = "";

    if (array_key_exists('attachment', $templates[$template])) {
        $attachment = "--attachment='" . $templates[$template]['attachment'] . "'";
    }

    $cmd = "cd emails && node send.js --production=production --displayName=\"$displayName\" --email=\"{$socio['email']}\" --updateCardUrl=\"{$socio['updateCardUrl']}\" --author=\"$author\" --from=\"{$author_email}\" --template=\"{$template}\" --subject=\"$subject\" $attachment";
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
<p>E-mail envoyé à <?php echo $displayName ?>.</p>
<?php else : ?>
<p>Envoyer à <?php echo $displayName ?>.</p>
<form method="POST" action="send.php">
<input type="hidden" name="id" value="<?php echo $_GET['id'] ?>" />
<p>
<select name="author">
<?php foreach($authors as $author => $email): ?>
  <?php if ($author == $_SERVER['REMOTE_USER']): ?>
    <option
     value="<?php echo $author ?>"
     selected="selected">
      <?php echo $author ?>
    </option>
  <?php else: ?>
    <option value="<?php echo $author ?>">
      <?php echo $author ?>
    </option>
  <?php endif; ?>
<?php endforeach; ?>
</select> Signature
</p>
<p><select name="template">
<?php foreach($templates as $id => $template): ?>
    <option value="<?php echo $id ?>"><?php echo $template['name'] ?></option>
<?php endforeach; ?>
</select> Modèle</p>
<p><input type="submit" value="Envoyer" /></p>
</form>
<?php endif; ?>
</div>
</html>
