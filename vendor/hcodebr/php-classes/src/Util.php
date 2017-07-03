<?php 

namespace Hcode;


//classe de métodos estáticos utilitários
class Util
{
	
	public static function errorPage($e)
	{
		$pageErr = new PageError(array(
			"header"=>false,
			"footer"=>false

		));

		$errCode = $e->getCode();
		
		$errMessage = $e->getMessage();

		switch($e->getCode())
		{
			case 0:
				$errCode = "";
				break;
			case 2002:
				$errMessage = "Ocorreu um erro. A conexão com o banco de dados foi recusada.";
				break;
		}
		
		$pageErr->setTpl("erro", array(
			"errorMessage"=>$errMessage,
			"errorNumber"=>$errCode			
			));
	}

}

 ?>