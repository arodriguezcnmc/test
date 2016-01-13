<!DOCTYPE html>
<html>
<head>
<meta charset="ISO-8859-1">
<title>Importación Contenidos</title>
<link href="css/bootstrap.min.css" rel="stylesheet">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/funciones.js"></script>
</head>
<body>
  <div class="container">
    <h3>Migración Contenidos DNN => Drupal:</h3>
    <div class="row">     
  		<ul>
        <li><a href="importar.php?contenido=novedades" onclick="return confirm('¿Seguro?')">Importar Novedades</a></li>
        <li><a href="importar.php?contenido=prensa" onclick="return confirm('¿Seguro?')">Importar Prensa</a></li>
        <li><a href="importar.php?contenido=agenda-presidente" onclick="return confirm('¿Seguro?')">Importar Agenda del Presidente</a></li>
    	</ul>
    </div>
	</div>  
</body>
</html>
