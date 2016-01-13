<?php
include('inc_config.php');
switch ($_GET['contenido']) {
  case 'agenda-presidente':
    $CagegoryID = 17;
    $texto_contenido = 'Agenda del Presidente';
    $sql = "SELECT COUNT(*) AS n FROM EasyDNNNews AS n INNER JOIN EasyDNNNewsEventsData AS e ON n.ArticleID = e.ArticleID  WHERE e.ArticleID IN (SELECT ArticleID FROM EasyDNNNewsCategories AS c INNER JOIN EasyDNNNewsCategoryList AS l ON c.CategoryID = l.CategoryID 	WHERE ParentCategory = $CagegoryID OR c.CategoryID = $CagegoryID)";

    break;
  case 'prensa':
    $CagegoryID = 10;
    $texto_contenido = 'Notas de Prensa';
    $sql = "SELECT COUNT(*) as n FROM EasyDNNNews WHERE ArticleID IN (SELECT ArticleID FROM EasyDNNNewsCategories AS c INNER JOIN EasyDNNNewsCategoryList AS l ON c.CategoryID = l.CategoryID 	WHERE ParentCategory = $CagegoryID OR c.CategoryID = $CagegoryID)";
    break;
  case 'novedades':
    $module_id = 1085;
    $texto_contenido = 'Novedades';
    $sql = "SELECT COUNT(*) AS n FROM UserDefinedRows WHERE ModuleId = '$module_id'";
    break;
  default:
    exit('Contenido no permitido');
}
$db->query($sql);
$db->next_record();
$total_registros = $db->record['n'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="ISO-8859-1">
<title>Importación Contenidos</title>
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/css.css" rel="stylesheet">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/funciones.js"></script>
<script type="text/javascript">
var registro = 0;
$(document).ready(function () {
  importar_registro('<?=$_GET['contenido']?>');
});
</script>
</head>
<body>
  <div class="container-fluid">
    <h3 class="text-center">Migración "<?=$texto_contenido?>" DNN => Drupal:</h3>
    <div class="row">
      <div class="col-md-12 text-center" id="info">  
     		Importando <span id="num_registros">0</span> registros de <?=$total_registros?><br />
  			<img src="imagenes/cargador.gif" alt="Cargando" />
  	  </div>
    </div>
    <div class="row">
      <div class="col-md-12 text-center text-error" id="errores"> 
  	  </div>
    </div>    
	</div>  
</body>
</html>
