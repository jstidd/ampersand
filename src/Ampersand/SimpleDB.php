<?php
namespace Ampersand;
use \PDO;
class SimpleDB 
{
	private $connection;


	public function __construct($servername, $database, $username, $password) {	


		try {
			$this->connection = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			echo "Database Connection Failed";
		}
	}


	public function select($query, $data = array()) {

		$q = $this->connection->prepare($query);
		$q->execute($data);
		return $q->fetchAll();			
	}

	/*
	 * This group of functions are the same except for thier name
	 * This to make reading code that uses this class easier to read
	 * */

	public function update($query, $data) {
		$q = $this->connection->prepare($query);
		return $q->execute($data);
	}

	public function insert($query, $data) {
		$q = $this->connection->prepare($query);
		return $q->execute($data);
	}

	public function delete($query, $data) {
		$q = $this->connection->prepare($query);
		return $q->execute($data);
	}

	private function execute($query, $data) {
		// returns true/false
		$q = $this->connection->prepare($query);
		return $q->execute($data);

	}

	/*
	 * return just the connection 
	 *
	 */
	public function rawConnection() {
		//returns connection

		return $this->connection;
	}
}
