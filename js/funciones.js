function importar_registro(contenido) {
  $.ajax ({
    type: 'POST',
    dataType: "html",
    cache: false,
    contentType: "application/x-www-form-urlencoded; charset=iso-8859-1",
    data: 'registro=' + registro++,
    url: 'ajax/' + contenido + '.php',
    success: function(data) {
      if (data == 'stop') {
        $('#info').html('Fin de la importaci&oacute;n<br /><br /><a href="index.php">Volver</a><br /><br />');
      } else {
        $('#num_registros').html((registro + 1) * 1);
        importar_registro(contenido);        
        if (data != 'ok') {
          $('#errores').append(data + '<br />');
        }
      }
    },
    error: function(data) {
      alert('Lo sentimos, se ha producido un error');
    }
  });
}