<?php 

namespace Hcode;

//classe genérica do modelo de dados
//com funções utilitárias para setters e getters
class Model
{
	private $values = [];

	//metodo magico invocado quando é chamado
	//um método set* ou get*
	public function __call($name, $args)
	{
		//verifica se é set ou get extraindo os três
		//primeiros caracteres do nome do método chamado
		$method = substr($name, 0, 3);

		//verifica qual atributo está sendo manipulado
		//extraindo os caracteres restantes do nome do método chamado
		$fieldName = substr($name, 3, strlen($name));

		//comportamento varia de acordo com o tipo de método
		switch ($method) 
		{
			case 'get':
				return $this->values[$fieldName];
				break;

			case 'set':
				$this->values[$fieldName] = $args[0];
				break;
			
			default:
				# code...
				break;
		}
	}

	//recebe um array associativo em que as chaves
	//são os nomes dos atributos
	public function setData($data = array())
	{
		foreach ($data as $key => $value) {

			//as chaves permitem ao PHP interpretar
			//a string concatenada como um método real
			$this->{"set".$key}($value);
		}


	}

	//retorna o array de atributos
	public function getValues()
	{
		return $this->values;

	}


}

 ?>