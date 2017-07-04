<?php 
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Util;

class User extends Model
{
	//constante usada para validar
	//a sessão do usuário
	const SESSION = "User";

	//constante para encriptar o código
	//de recuperação de senha do usuário
	const SENHA = "SenhaTeste782134";
	

	//login do usuário no sistema
	public static function login($login, $password)
	{
		$sql = new Sql();

		//query para procurar o login fornecido
		$results = $sql->select(
				"SELECT * FROM tb_users WHERE deslogin = :LOGIN", 
				array(
					":LOGIN" =>$login
					)
		);

		//valida login existente
		Util::checkEmptyArray($results, "Usuário inexistente ou senha inválida");

		//array com os dados recuperados 
		//em caso de retorno válido da 
		//busca pelo login fornecido
		$data = $results[0];

		//checa se o hash da senha fornecida bate com o que está
		//registrado no banco de dados
		if (password_verify($password, $data["despassword"]) === true)
		{
			//caso a senha seja válida, instancia um objeto User
			$user = new User();

			//configura as variáveis com os dados retornados
			$user->setData($data);

			//configura a variável de sessão com os dados
			//do usuário
			$_SESSION[User::SESSION] = $user->getValues();

			//retorna o objeto User
			return $user;


		} else{
			throw new \Exception("Usuário inexistente ou senha inválida");
		}

	}


	public static function verifyLogin($inadmin = true)
	{
		if (
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int) $_SESSION[User::SESSION]["iduser"] > 0
			||
			(bool) $_SESSION[User::SESSION]["inadmin"] !== $inadmin
		) {

			header("Location: /admin/login");
			exit;

		}

	}


	public static function logout()
	{
		$_SESSION[User::SESSION] = NULL;
	}


	public static function listAll()
	{

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");

		 

	}

	public function get($iduser)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING (idperson) WHERE b.iduser = :iduser ORDER BY a.desperson", array(
				":iduser"=>$iduser
			));

		$this->setData($results[0]);
	}

	public function save()
	{


		$sql = new Sql();

		$result = $sql->select("CALL sp_users_save (:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",  array(
				":desperson"=>$this->getdesperson(),
				":deslogin"=>$this->getdeslogin(),
				":despassword"=>password_hash(
										$this->getdespassword(),
										PASSWORD_DEFAULT,
										array("cost"=>12)),
				":desemail"=>$this->getdesemail(),
				"nrphone"=>$this->getnrphone(),
				"inadmin"=>$this->getinadmin()
			));

		$this->setData($result[0]);


	}

	public function saveUpdate()
	{
		$sql = new Sql();

		$result = $sql->select("CALL sp_usersupdate_save (:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",  array(
				":iduser"=>$this->getiduser(),
				":desperson"=>$this->getdesperson(),
				":deslogin"=>$this->getdeslogin(),
				":despassword"=>password_hash(
										$this->getdespassword(),
										PASSWORD_DEFAULT,
										array("cost"=>12)),
				":desemail"=>$this->getdesemail(),
				"nrphone"=>$this->getnrphone(),
				"inadmin"=>$this->getinadmin()
			));

		$this->setData($result[0]);

	}

	public function delete()
	{
		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
			));


	}

	public static function generateRecoveryCode($email)
	{
		//instancia da classe de acesso ao banco de dados
		$sql = new Sql();

		//consulta o email fornecido
		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING (idperson) WHERE b.desemail = :email", array(

				":email"=>$email

			));

		
		//checa se houve retorno de resultados da consulta, em caso negativo
		//lança uma Exception
		Util::checkEmptyArray($results, "Não foi possível recuperar a senha");

		
		$data = $results[0];
		
		//gera o codigo de recuperacao
		$results2 = $sql->select("call sp_userspasswordsrecoveries_create(:iduser, :ip)", array(

				":iduser"=>$data["iduser"],
				":ip"=>$_SERVER["REMOTE_ADDR"]

			));

		//checa se houve retorno de resultados da consulta, em caso negativo
		//lança uma Exception
		Util::checkEmptyArray($results2, "Não foi possível recuperar a senha");

		
		$datarecovery = $results2[0];

		//criptografa o codigo de recuperacao e transforma em string base64
		$code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SENHA, $datarecovery["idrecovery"], MCRYPT_MODE_ECB));

		//hiperlink para que o usuário acesse a página de alteração de senha
		$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";

		//configura a classe de envio de email
		$mailer = new Mailer($data["desemail"], 
							 $data["desperson"],
							 "Recuperação de Senha Hcode Store",
							 "forgot",
							 array(
						 		"name"=>$data["desperson"],
						 		"link"=>$link
							 	));

		//envia o email
		$mailer->send();

		return $data;

	}

	//checa se o código de recuperação de senha é válido
	public static function verifyRecoveryCode($code)
	{

		$sql = new Sql();
		
		//descriptografa o código recebido
		$idrecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,
									 User::SENHA, 
									 base64_decode($code), 
									 MCRYPT_MODE_ECB);

		$dataRec = $sql->select("
				SELECT * FROM tb_userspasswordsrecoveries a INNER JOIN tb_users b
				USING (iduser) INNER JOIN tb_persons c
				USING (idperson)
				WHERE
				a.idrecovery = :idrecovery
				AND
				a.dtrecovery IS NULL
				AND
				(DATE_ADD(a.dtregister, INTERVAL 1 HOUR) > NOW())", array(
					":idrecovery"=>$idrecovery
				));

		
		//checa se houve retorno de resultados da consulta, em caso negativo
		//lança uma Exception
		Util::checkEmptyArray($dataRec, "Não foi possível recuperar a senha");

		return $dataRec[0];


	}

	//usada quando o usuário reseta a senha.
	//só funciona no contexto de recuperação de senha
	//em que o usuário recebe um idrecovery para resetar
	//a senha
	private function setUsedRecoveryCode()
	{

		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE iduser = :iduser AND idrecovery = :idrecovery" , array(
				":iduser"=>$this->getiduser(),
				":idrecovery"=>$this->getidrecovery()
			));


	} 

	//altera a senha do usuário
	public function resetPassword($password)
	{
		//indicador de sucesso da transação
		$status = false;		

		//invalida o código de recuperação para
		//que não seja mais usado
		$this->setUsedRecoveryCode();

		$sql = new Sql();

		//cria o hash da senha
		$hash = password_hash($password, PASSWORD_DEFAULT, array("cost"=>12));

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser" , array(
				":password"=>$hash,
				":iduser"=>$this->getiduser()
			));
		
		//se der tudo certo até aqui o status
		//é alterado para indicar sucesso na
		//operação
		$status = true;

		return $status;
	}
}


 ?>