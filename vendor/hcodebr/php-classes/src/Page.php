<?php 

namespace Hcode;

use Rain\Tpl;

class Page{

	private $tpl;
	private $options = [];

	//array de configurações padrão
	private $defaults = [
		"header"=>true,
		"footer"=>true,
		"data" => []
	];

	public function __construct($opts = array(), $tpl_dir="/views/"){

		//mescla as opções recebidas no construtor com os defaults
		//prevalecendo as que forem recebidas como parâmetro
		$this->options = array_merge($this->defaults, $opts);

		//array de configurações do Tpl
		$config = array(
			"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"].$tpl_dir,
			"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
			"debug"         => false // set to false to improve the speed
	   	);

		Tpl::configure( $config );

		$this->tpl = new Tpl;

		//configura as variáveis do template de forma dinâmica
		$this->setData($this->options["data"]);

		//ao fim do construtor desenha o cabeçalho do template,  caso
		//o usuário da classe assim o escolha na opção "header"
		if($this->options["header"]==true) $this->tpl->draw("header");

	}

	//método para configurar as variáveis do template
	//de modo dinâmico
	private function setData($data=array())
	{
		
		
		foreach ($data as $key => $value) {
			$this->tpl->assign($key, $value);
		}

	}

	//configura e desenha um template 
	public function setTpl($name, $data=array(), $returnHTML = false)
	{

		$this->setData($data);

		return $this->tpl->draw($name, $returnHTML);

	}

	//ao destruir a instância do objeto deseja um rodapé,
	//caso desejado pelo usuário da classe
	public function __destruct(){

		if($this->options["footer"]==true) $this->tpl->draw("footer");
	}
}

 ?>