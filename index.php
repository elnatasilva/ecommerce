<?php 
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;


$app = new \Slim\Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});

$app->get('/admin', function() {

    User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("index");

});

$app->get('/admin/login', function() {

    
	$page = new PageAdmin(array(
			"header"=>false,
			"footer"=>false

		));

	$page->setTpl("login");

});

$app->post('/admin/login', function() {

    User::login($_POST["login"], $_POST["password"]);
	
	header("Location: /admin");

	exit;

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

	User::verifyLogin();

	$_POST["inadmin"] = (isset($_POST["inadmin"])?1:0);

	$user = new User();

	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");

	exit;
	
});

$app->get('/admin/users/:iduser/delete', function($iduser){

	User::verifyLogin();	

	$user = new User();

	$user->get($iduser);

	$user->delete();

	header("Location: /admin/users");

	exit;

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

	User::verifyLogin();	

	$user = new User();

	$user->get($iduser);

	$user->setData($_POST);

	$user->saveUpdate();

	header("Location: /admin/users");

	exit;	


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

	$email = $_POST['email'];

	$data = User::generateRecoveryCode($email);
	
	header("Location: /admin/forgot/sent");

	exit;

});

$app->get("/admin/forgot/sent", function(){

	$page = new PageAdmin(array(
			"header"=>false,
			"footer"=>false

		));

	$page->setTpl("forgot-sent");

	exit;


});

$app->get("/admin/forgot/reset/:idrecovery", function($idrecovery){

	$dataRec = User::verifyRecoveryCode($idrecovery);

	var_dump($dataRec);

	exit;

	$page = new PageAdmin(array(
			"header"=>false,
			"footer"=>false

		));

	$page->setTpl("forgot-reset", array(
			"name"=>$dataRec["desperson"],
			"code"=>$idrecovery
		));


	exit;
});


$app->run();

 ?>