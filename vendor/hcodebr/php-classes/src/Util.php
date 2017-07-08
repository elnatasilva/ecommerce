<?php 

namespace Hcode;


//classe de métodos estáticos utilitários
class Util
{
	
	//função para exibir uma página de
	//erro padrão
	public static function errorPage($e, $rota="/admin/login", $textoLink="Voltar para login")
	// public static function errorPage($e)
	{
		$pageErr = new PageError(array(
			"header"=>false,
			"footer"=>false

		));

		$errCode = $e->getCode();
		
		$errMessage = $e->getMessage();

		$errMessage = $e->getTraceAsString() . "\nhash: " . password_hash(
														"admin",
														PASSWORD_DEFAULT,
														array("cost"=>12));

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
			"errorNumber"=>$errCode,
			"rota"=>$rota,
			"textoLink"=>$textoLink
			));
	}

	//checa se um array é vazio e lança uma Exception
	//caso seja vazio
	public static function checkEmptyArray($array, $message)
	{

		if (count($array) === 0)
		{
			throw new \Exception($message);
			
		}

	}

}

 ?>