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

<h2>Démarchage téléphonique</h2>

<input type="button" name="save" id="save" value="Enregistrer" />

<div id="toolbar">
		<select class="form-control">
				<option value="">Export simple</option>
				<option value="all">Exporter tout</option>
				<option value="selected">Exporter la sélection</option>
		</select>
</div>

<table id="table"
             data-search="true"
			 data-filter-control="true" 
			 data-show-export="true"
			 data-click-to-select="true"
             data-escape="false"
			 data-toolbar="#toolbar"
             data-pagination="true"
             data-page-size="100"
             data-url="get.php"
             data-id-field="id"
             data-editable-url="save.php"
             data-editable-pk="1">
<!--	<thead>
		<tr>
			<th data-field="state" data-checkbox="true"></th>
            <th data-field="id">ID</th>
            <th data-field="email">E-mail</td>
            <th data-field="nom">Nom</td>
            <th data-field="facturation">Facturation</td>
            <th data-field="echeance" data-sortable="true">Echéance</td>
            <th data-field="expirationcb" data-sortable="true">Expiration CB</td>
            <th data-field="montant" data-sortable="true">Montant</td>
            <th data-field="telephone">Téléphone</td>
            <th data-escape="false" data-searchable="false" date-field="url">URL</td>
            <th data-field="done" data-searchable="false" date-editable="true">Fait</td>
            <th data-field="observations" data-escape="true" data-searchable="true" data-editable="true">Observations</td>
		</tr>
	</thead>-->
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

$(function() {
    $table.bootstrapTable({
      idField: 'id',
      toggle: 'table',
      filerControl: true,
      showExport: true,
      toolbar: "#toolbar",
      pagination: true,
      pageSize: 100,
      url: "get.php",
      columns: [{
        field: 'state',
        title: '',
        checkbox: true
      }, {
        field: 'id',
        title: 'ID',
        sortable: false,
      }, {
        field: 'id',
        title: 'ID',
        sortable: false,
        editable: false
      }, {
        field: 'email',
        title: 'E-mail',
        sortable: false,
        editable: false,
        searchable: true
      }, {
        field: 'nom',
        title: 'Nom',
        sortable: false,
        editable: false,
        searchable: true
      }, {
        field: 'facturation',
        title: 'Facturation',
        sortable: true,
        editable: false
      }, {
        field: 'echeance',
        title: 'Échéance',
        sortable: true,
        editable: false
      }, {
        field: 'expirationcb',
        title: 'Expiration',
        sortable: true,
        editable: false
      }, {
        field: 'montant',
        title: 'Montant',
        sortable: true,
        editable: false
      }, {
        field: 'telephone',
        title: 'Téléphone',
        sortable: false,
        editable: false,
        searchable: true
      }, {
        field: 'url',
        title: 'URL',
        sortable: false,
        editable: false,
        escape: false
      }, {
        field: 'done',
        title: 'Traité',
        sortable: false,
        editable: {
          type: 'select',
          source: [
              {value: 0, text: 'Non'},
              {value: 1, text: 'Oui'},
              {value: 2, text: 'Refus'}
          ]
        }
      }, {
        field: 'observations',
        title: 'Observations',
        sortable: false,
        editable: {
          type: 'textarea'
        }
      }
      ],
    })
  })

$(function () {
  $('#toolbar').find('select').change(function () {
    $table.bootstrapTable('refreshOptions', {
      exportDataType: $(this).val() });

  });
});

$('#table').on('editable-save.bs.table', function(e, field, row, oldValue, $el){
    console.log(field);
    console.log(row);
    console.log(oldValue);

    $.ajax({
       type: "POST",
       url: "save.php",
       dataType: "json",
       success: function (msg) {
           if (msg) {
               console.log(msg);
           } else {
               alert("Échec.");
           }
       },

       data: {field: field, row: row}
   });
})

var trBoldBlue = $("table");

$(trBoldBlue).on("click", "tr", function () {
  $(this).toggleClass("bold-blue");
});
    </script>
</body>

</html>
