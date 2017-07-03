<?php 
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\PageError;
use \Hcode\Util;
use \Hcode\Model\User;


$app = new \Slim\Slim();

$app->config('debug', true);


//rota da homepage
$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});


//acesso ao menu administrativo
$app->get('/admin', function() {

    User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("index");

});


//login ao menu administrativo
$app->get('/admin/login', function() {

    //o template tem seu próprio header
    //e footer, por isso é necessário
    //desabitiltar a chamada aos templates
    //do construtor da classe PageAdmin
	$page = new PageAdmin(array(
			"header"=>false,
			"footer"=>false

		));

	$page->setTpl("login");

});

//rota de processamento do login
$app->post('/admin/login', function() {

	try
	{

	    User::login($_POST["login"], $_POST["password"]);
		
		header("Location: /admin");

	}catch (Exception $e)
	{

		//classe utilitária que mostra uma mensagem de erro 
		//formatada
		Util::errorPage($e);

	}finally
	{
		exit;
	}

	

});

$app->get('/admin/logout', function() {

    User::logout();

    header("Location: /admin/login");

	exit;

});

$app->get('/admin/users', function(){

	User::verifyLogin();

	$users = User::listAll();

	$page = new PageAdmin();

	$page->setTpl("users", array(
		"users"=>$users
		));

	exit;
	
});

$app->get('/admin/users/create', function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");

	exit;
	
});

$app->post('/admin/users/create', function(){

	try
	{

		User::verifyLogin();

		$_POST["inadmin"] = (isset($_POST["inadmin"])?1:0);

		$user = new User();

		$user->setData($_POST);

		$user->save();

		header("Location: /admin/users");

	}catch(Exception $e)
	{
		Util::errorPage($e);

	}finally
	{

		exit;

	}
	
});

$app->get('/admin/users/:iduser/delete', function($iduser){

	try
	{
		User::verifyLogin();	

		$user = new User();

		$user->get($iduser);

		$user->delete();

		header("Location: /admin/users");
	}catch(Exception $e)
	{
		Util::errorPage($e);

	}finally
	{

		exit;

	}

});

$app->get('/admin/users/:iduser', function($iduser){

	User::verifyLogin();	

	$user = new User();

	$user->get($iduser);

	$page = new PageAdmin();

	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
		));

	exit;	


});


$app->post('/admin/users/:iduser', function($iduser){

	try
	{
		User::verifyLogin();	

		$user = new User();

		$user->get($iduser);

		$user->setData($_POST);

		$user->saveUpdate();

		header("Location: /admin/users");
	}catch(Exception $e)
	{
		Util::errorPage($e);

	}finally
	{

		exit;

	}


});


$app->get("/admin/forgot", function(){

	$page = new PageAdmin(array(
			"header"=>false,
			"footer"=>false

		));

	$page->setTpl("forgot");

	exit;


});


$app->post("/admin/forgot", function(){

	try
	{
		$email = $_POST['email'];

		$data = User::generateRecoveryCode($email);
		
		header("Location: /admin/forgot/sent");
	}catch(Exception $e)
	{
		Util::errorPage($e);

	}finally
	{

		exit;

	}

});

$app->get("/admin/forgot/sent", function(){

	$page = new PageAdmin(array(
			"header"=>false,
			"footer"=>false

		));

	$page->setTpl("forgot-sent");

	exit;


});


$app->post("/admin/forgot/reset", function(){

	$dataRec = User::verifyRecoveryCode($_POST["code"]);

	$user = new User();

	$user->setData($dataRec);

	if (!$user->resetPassword($_POST["password"]))
		throw new \Exception("Não foi possível redefinir a senha");
		 
	$page = new PageAdmin(array(
			"header"=>false,
			"footer"=>false

		));

	$page->setTpl("forgot-reset-success");

	exit;
	
});



$app->get("/admin/forgot/reset", function(){

	$dataRec = User::verifyRecoveryCode($_GET["code"]);

	$page = new PageAdmin(array(
			"header"=>false,
			"footer"=>false

		));

	$page->setTpl("forgot-reset", array(
			"name"=>$dataRec["desperson"],
			"code"=>$_GET["code"]
		));


	exit;

});





$app->run();

 ?>