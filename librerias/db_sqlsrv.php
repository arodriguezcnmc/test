<?php
class db_sqlsrv {
  private $server = 'SQLDESA2012\sqldesa2012';
  private $user = 'Webv2_LoginOwner';
  private $pw = 'Barquillo5';
  private $db = 'db_Webv2';
	private $conn = null;
	private $row = null;
	public $stmt = null;
	public $record = array();

	// Conecta con la bbdd
  private function connect() {
    $this->conn = sqlsrv_connect($this->server, array( "Database" => $this->db, "UID" => $this->user, "PWD" => $this->pw));
	  if($this->conn === false) {
	    die( print_r( sqlsrv_errors(), true));
	  }
  }
	
  // Lanza una consulta SQL
	public function query($sql) {
	  $this->connect();
	  $this->stmt = sqlsrv_query($this->conn, $sql);
	  if($this->stmt === false) {
	    die( print_r( sqlsrv_errors(), true) );
	  }	  
	}
	
	// Recorre el siguiente registro de la consulta realizada
	public function next_record() {
	  $this->record = sqlsrv_fetch_array($this->stmt, SQLSRV_FETCH_ASSOC);  	
	  if ($this->record === false) {
		$this->record = array();
	    $this->free_result();	  
	  }
	  return $this->record;
	}
	
	// Libera el puntero de los registros obtenidos
	public function free_result() {
	  return sqlsrv_free_stmt($this->stmt);
	}
  
	// Devuelve los nombres de los campos de una consulta realizada
  public function fields() {
    return sqlsrv_field_metadata($this->stmt);
  }
}
?>