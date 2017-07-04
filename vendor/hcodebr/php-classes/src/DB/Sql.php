<?php 

namespace Hcode\DB;

class Sql {

	const HOSTNAME = "127.0.0.1";
	const USERNAME = "root";
	const PASSWORD = "";
	const DBNAME = "db_ecommerce";

	private $conn;

	public function __construct()
	{

		$this->conn = new \PDO(
			"mysql:dbname=".Sql::DBNAME.";host=".Sql::HOSTNAME, 
			Sql::USERNAME,
			Sql::PASSWORD
		);

	}

	//funcao invocada em todos os comandos para passar um
	//array de parametros que serão substituidos na string
	//do comando
	private function setParams($statement, $parameters = array())
	{

		foreach ($parameters as $key => $value) {
			
			$this->bindParam($statement, $key, $value);

		}

	}

	//substitui um parametro pelo valor fornecido
	private function bindParam($statement, $key, $value)
	{

		$statement->bindParam($key, $value);

	}

	//executa um comando SEM retorno de dados
	public function query($rawQuery, $params = array())
	{
		// echo "Entrou no query com o comando " . $rawQuery;
		$stmt = $this->conn->prepare($rawQuery);

		$this->setParams($stmt, $params);

		$stmt->execute();

	}

	//executa um comando COM retorno de dados
	public function select($rawQuery, $params = array())
	{

		$stmt = $this->conn->prepare($rawQuery);

		$this->setParams($stmt, $params);

		$stmt->execute();

		return $stmt->fetchAll(\PDO::FETCH_ASSOC);

	}

}

 ?>