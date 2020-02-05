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
//print_r($records);
?>

<!DOCTYPE html>
<html lang="en" >

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

table { border-collapse: collapse;}
td { padding: 0px 4px; border: 1px solid black; font-size: 80%; }
tr { padding: 0; margin: 0; }
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

<h2>Démarchage téléphonique</h2>

<input type="button" name="save" id="save" value="Enregistrer" />

<div id="toolbar">
		<select class="form-control">
				<option value="">Export Basic</option>
				<option value="all">Export All</option>
				<option value="selected">Export Selected</option>
		</select>
</div>

<table id="table" 
			 data-toggle="table"
             data-search="true"
			 data-filter-control="true" 
			 data-show-export="true"
			 data-click-to-select="true"
             data-escape="false"
			 data-toolbar="#toolbar">
	<thead>
		<tr>
			<th data-field="state" data-checkbox="true"></th>
            <th data-field="email">E-mail</td>
            <th data-field="prenom">Prénom</td>
            <th data-field="nom">Nom</td>
            <th data-field="facturation">Facturation</td>
            <th data-field="echeance" data-sortable="true">Echéance</td>
            <th data-field="expirationcb" data-sortable="true">Expiration CB</td>
            <th data-field="montant" data-sortable="true">Montant</td>
            <th data-field="telephone">Téléphone</td>
            <th data-escape="false" data-searchable="false">URL</td>
            <th data-escape="false" data-searchable="false">Fait</td>
            <th data-escape="false" data-searchable="false">Observations</td>
		</tr>
	</thead>
	<tbody>
    <?php foreach($socios as $socio) : ?>
    <tr class="socio" id="<?php echo $socio['chargebee_id'] ?>">
		<td class="bs-checkbox "><input data-index="0" name="btSelectItem" type="checkbox"></td>
        <td><?php echo $socio['email'] ?></td>
        <td><?php echo $socio['firstname'] ?></td>
        <td><?php echo $socio['lastname'] ?></td>
        <td><?php echo $socio['billing_period_unit'] ?></td> 
        <td><?php echo date('Y-m-d', $socio['next_billing_at']/1000) ?></td>
        <td><?php echo date('Y-m', $socio['card_expiry']/1000) ?></td>
        <td><?php echo $socio['amount'] ?> €</td>
        <td><?php echo format_phone($socio['phone']) ?></td>
        <td><a href="<?php echo str_replace('https://lemediatv.fr', 'https://www.lemediatv.fr', $socio['updateCardUrl']) ?>" target="_blank">url</a></td>
        <td><input type="checkbox" <?php if (is_done($socio['chargebee_id'])) { echo 'checked="checked"'; } ?> class="is_done" /></td>
        <td><textarea class="observations" cols="30" rows="2"><?php echo htmlspecialchars(observations($socio['chargebee_id'])) ?></textarea></td>
    </tr>
    <?php endforeach; ?>
	</tbody>
</table>
</div>
    <script src="https://static.codepen.io/assets/common/stopExecutionOnTimeout-db44b196776521ea816683afab021f757616c80860d31da6232dedb8d7cc4862.js"></script>

  <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.10.0/bootstrap-table.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.9.1/extensions/editable/bootstrap-table-editable.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.9.1/extensions/export/bootstrap-table-export.js'></script>
<script src='https://rawgit.com/hhurz/tableExport.jquery.plugin/master/tableExport.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.9.1/extensions/filter-control/bootstrap-table-filter-control.js'></script>
  
      <script id="rendered-js" >
//exporte les données sélectionnées
var $table = $('#table');
$(function () {
  $('#toolbar').find('select').change(function () {
    $table.bootstrapTable('refreshOptions', {
      exportDataType: $(this).val() });

  });
});

var trBoldBlue = $("table");

$(trBoldBlue).on("click", "tr", function () {
  $(this).toggleClass("bold-blue");
});
//# sourceURL=pen.js
    </script>
</body>

</html>
