<?php
class db_drupal {
    private $server = 'localhost';
    private $user = 'root';
    private $pw = '';
    private $db = 'drupal';
	private $conn = null;
	private $stmt = null;
	private $row = null;
	public $record = array();

    private function connect() {
      $this->conn = mysql_connect($this->server, $this->user, $this->pw, $this->db);
	  if ($this->conn === false) {
	    die('No pudo conectarse: ' . mysql_error());
	  }
	  if (!mysql_select_db($this->db, $this->conn)) {
		echo 'No pudo seleccionar la base de datos';
		exit;
	  }	  
    }
	
	public function query($sql) {
	  $this->connect();
	  $this->stmt = mysql_query($sql, $this->conn);
	  if($this->stmt === false) {
	    echo "Error de BD, no se pudo consultar la base de datos\n";
		echo "Error MySQL: " . mysql_error();
		exit;
	  }	  
	}
	
	
	public function insert($sql) {
	  $this->query($sql);
	  return mysql_insert_id();
	}
	
	public function next_record() {
	  $this->record = mysql_fetch_assoc($this->stmt);  	
	  if ($this->record === false) {
		$this->record = array();
	    $this->free_result();	  
	  }
	  return $this->record;
	}
	
	public function free_result() {
	  return mysql_free_result($this->stmt);
	}
}
?>