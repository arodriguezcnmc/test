<?php
include('librerias/db_sqlsrv.php');

$db = new db_sqlsrv;
$db2 = new db_sqlsrv;

extract($_GET);
?>
<style type="text/css">
  table {border-collapse: collapse; cellspacing: none}
  th, td {text-align:left; padding: 4px; border: 1px solid #ccc}
</style>
<?php
switch($t) {
	case 'novedades':
	  $ModuleId = 1085;
	  $Tabla = 'Novedades';
	break;
	default:
	  $ModuleId = 1085;
	  $Tabla = 'Novedades';	  
}
?>
<strong>Definición de tabla "<?=$Tabla?>":</strong><br />
<table>
  <tr>
    <th>Nombre</th>
    <th>Visible</th>
    <th>Tipo</th>
    <th>Obligatorio</th>
    <th>Predeterminado</th>
  </tr>
<?php
// Sacamos la definición de la tabla
$db->query("SELECT * FROM UserDefinedFields WHERE ModuleId = '$ModuleId'");
while($db->next_record()) {
  $campos[$db->record['UserDefinedFieldId']] = $db->record['FieldTitle'];
?>	
  <tr>
    <td><?=$db->record['FieldTitle']?></td>
    <td><?=$db->record['Visible'] ? 'Sí' : 'No'?></td>
    <td>
	  <?=$db->record['FieldType']?>
<?php
  if ($db->record['InputSettings']) {
	echo ' (' . $db->record['InputSettings'] . ')';
  }
?>  
	</td>
    <td><?=$db->record['Required'] ? 'Sí' : 'No'?></td>
    <td><?=$db->record['Default']?></td>
  </tr> 
<?php	  
}
?>
</table>
<br /><br />
<?php
// Imprimimos los registros de la tabla
$db->query("SELECT COUNT(*) AS n FROM UserDefinedRows WHERE ModuleId = '$ModuleId'");
$db->next_record();
?>
<strong>Datos de tabla "<?=$Tabla?>" (<?=$db->record['n']?> registros):</strong><br />
<table>
  <tr>
    <th>ID</th>
<?php
foreach($campos as $campo) {
?>
    <th><?=$campo?></th>
<?php	
}
?>
  </tr>
<?php

  $db->query("SELECT * FROM UserDefinedRows WHERE ModuleId = '$ModuleId'");
  while($db->next_record()) {
?>	
  <tr>
    <td><?=$db->record['UserDefinedRowId']?></td>  
<?php
    foreach ($campos as $UserDefinedFieldId => $campo) {
	  $db2->query("SELECT * FROM UserDefinedData WHERE UserDefinedRowId = '{$db->record['UserDefinedRowId']}' AND UserDefinedFieldId = '$UserDefinedFieldId'");
	  $db2->next_record();
?>  
    <td><?=$db2->record['FieldValue']?></td>
<?php	
	}
?>
  </tr> 
<?php	
  }  
?>
</table>

