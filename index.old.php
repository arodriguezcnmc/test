<style type="text/css">
  table {border-collapse: collapse; cellspacing: none}
  th, td {text-align:left; padding: 4px; border: 1px solid #ccc}
</style>
<?php
$serverName = "SQLDESA2012\sqldesa2012";
$connectionInfo = array( "Database"=>"db_Webv2", "UID"=>"Webv2_LoginOwner", "PWD"=>"Barquillo5");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if($conn === false) {
    die( print_r( sqlsrv_errors(), true));
}

extract($_GET);
switch($t) {
	case 'novedades':
	  $ModuleId = 2721;
	  $Tabla = 'Novedades';
	break;
	default:
	  $ModuleId = 2721;
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
$sql = "SELECT * FROM UserDefinedFields WHERE ModuleId = '$ModuleId'";
$stmt = sqlsrv_query($conn, $sql);
if($stmt === false) {
  die( print_r( sqlsrv_errors(), true) );
}
$campos = array();
while( $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) ) {
  $campos[$row['UserDefinedFieldId']] = $row['FieldTitle'];
?>	
  <tr>
    <td><?=$row['FieldTitle']?></td>
    <td><?=$row['Visible'] ? 'Sí' : 'No'?></td>
    <td>
	  <?=$row['FieldType']?>
<?php
  if ($row['InputSettings']) {
	echo ' (' . $row['InputSettings'] . ')';
  }
?>  
	</td>
    <td><?=$row['Required'] ? 'Sí' : 'No'?></td>
    <td><?=$row['Default']?></td>
  </tr> 
<?php	  
}
sqlsrv_free_stmt( $stmt);
?>
</table>
<br /><br />
<?php
$sql = "SELECT COUNT(*) AS n FROM UserDefinedRows WHERE ModuleId = '$ModuleId'";
$stmt = sqlsrv_query($conn, $sql);
if($stmt === false) {
  die( print_r( sqlsrv_errors(), true) );
}
$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
sqlsrv_free_stmt($stmt);
?>
<strong>Datos de tabla "<?=$Tabla?>" (<?=$row['n']?> registros):</strong><br />
<table>
  <tr>
<?php
foreach($campos as $campo) {
?>
    <th><?=$campo?></th>
<?php	
}
?>
  </tr>
<?php
foreach($campos as $campo) {
  $sql = "SELECT TOP 20 * FROM UserDefinedRows WHERE ModuleId = '$ModuleId'";
  $stmt = sqlsrv_query($conn, $sql);
  if($stmt === false) {
    die( print_r( sqlsrv_errors(), true) );
  }
  while( $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) ) {
?>	
  <tr>
<?php
    foreach ($campos as $UserDefinedFieldId => $campo) {
	  $sql2 = "SELECT * FROM UserDefinedData WHERE UserDefinedRowId = '{$row['UserDefinedRowId']}' AND UserDefinedFieldId = '$UserDefinedFieldId'";
	  $stmt2 = sqlsrv_query($conn, $sql2);
	  if($stmt2 === false) {
		die( print_r( sqlsrv_errors(), true) );
	  }
	  $row2 = array();
	  $row2 = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC); 		
?>  
    <td><?=$row2['FieldValue']?></td>
<?php	
	  sqlsrv_free_stmt($stmt2);
	}
?>
  </tr> 
<?php	
  }  
}
sqlsrv_free_stmt($stmt);
?>
</table>

