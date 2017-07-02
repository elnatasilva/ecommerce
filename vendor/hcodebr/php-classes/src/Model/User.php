<?php 
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model
{
	const SESSION = "User";

	const SENHA = "SenhaTeste782134";
	
	public static function login($login, $password)
	{
		$sql = new Sql();

		$results = $sql->select(
				"SELECT * FROM tb_users WHERE deslogin = :LOGIN", 
				array(
					":LOGIN" =>$login
					)
		);

		if (count($results) === 0)
		{
			throw new \Exception("Usuário inexistente ou senha inválida");
			
		}

		$data = $results[0];

		//metodo antigo
		// if (password_verify($password, $data["despassword"]) === true)
		// if (crypt($password, User::SENHA) === $data["despassword"])

		if (password_verify($password, $data["despassword"]) === true)
		{
			$user = new User();

			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();

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

		if (count($results) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha");
			
		}else
		{
			$data = $results[0];
			
			//gera o codigo de recuperacao
			$results2 = $sql->select("call sp_userspasswordsrecoveries_create(:iduser, :ip)", array(

					":iduser"=>$data["iduser"],
					":ip"=>$_SERVER["REMOTE_ADDR"]

				));

			if (count($results2) === 0)
			{
				throw new \Exception("Não foi possível recuperar a senha");
				
			}else
			{

				$datarecovery = $results2[0];

				//criptografa o codigo de recuperacao e transforma em string base64
				$link = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SENHA, $datarecovery["idrecovery"], MCRYPT_MODE_ECB));


				$link = "http://www.hcodecommerce.com.br/admin/forgot/reset/" . $link;

				//configura a classe de envio de email
				$mailer = new Mailer($data["desemail"], 
									 $data["desperson"],
									 "Recuperação de Senha Hcode Store",
									 "forgot",
									 array(
								 		"name"=>$data["desperson"],
								 		"link"=>$link
									 	));

				$mailer->send();

				return $data;
			}
		}

	}

	public static function verifyRecoveryCode($code)
	{

		$sql = new Sql();
		
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

		if (count($dataRec) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha, code = $code, codigo $idrecovery");
			
		}

		return $dataRec;


	}
}


 ?>