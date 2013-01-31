<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

require 'Slim/Slim.php';
require 'Slim/Middleware.php';
require 'AuthMiddleware.class.php';

require 'MongoWrapper.class.php';

require_once('../AppInfo.php');

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
$app->add(new \AuthMiddleware());
$app->item_address = AppInfo::getUrl()."prototype/app.php#page2?item_id=";

$mongo = new \MongoWrapper\MongoWrapper(getenv("MONGOHQURL"));

$mongo->setDatabase("bookschange");



$app->get('/notifications/:limit',function($limit) use ($app,$mongo){
	$app->response()->header("Content-Type", "application/json");
	
	$mongo->setCollection("notifications");

	$result = $mongo->getAll(array(),$limit);

	echo json_encode($result);

});


$app->post('/notifications/',  function() use($app, $mongo){
	$request = $app->request();

	$item = json_decode($request->getBody());
	$item["fb_id"] = $app->fb_id;

	$mongo->setCollection("notifications");

	$mongo->insert($item);
});


$app->put('/notifications/:id', function($id) use($app, $mongo){
	$request = $app->request();

	$item = json_decode($request->getBody());

	$mongo->setCollection("notifications");

	$mongo->update(array("_id"=>new MongoId($id)), $item);
});

$app->delete('/notifications/:id', function($id) use($app, $mongo){
	$request = $app->request();

	$item = json_decode($request->getBody());

	$mongo->setCollection("notifications");

	$mongo->delete(array("_id"=>new MongoId($id)));
});


$app->get('/items/:id', function($id) use($app, $mongo){
	$mongo->setCollection("items");

	$result = $mongo->get(array("_id"=>new MongoId($id)));

	echo json_encode($result);
});

$app->get('/items/recommendations/:limit', function($limit) use($app, $mongo){
	$mongo->setCollection("usuarios");
	$user = $mongo->find(array("fb_id"=>$app->fb_id));


	
	$mongo->setColection("items");
	
	//Get in genres collection what we have in user genres
	$crietria = array("genre"=>array("$in"=>$user['genre']));

	$result = $mongo->get($criteria ,$limit);

	echo json_encode($result);
});

$app->get('/items/search/:query', function($search_query) use($app, $mongo){
	$mongo->setCollection("items");
	
	$criteria = array('$or'=>array(array("genre"=>new MongoRegex("/$search_query/i")),array("title"=>new MongoRegex("/$search_query/i"))));
	$result = $mongo->get($criteria, 100);

	echo json_encode($result);
});

$app->post('/items/',  function() use($app, $mongo){


	$request = $app->request();

	$item = json_decode($request->getBody());
	
	//customize item

	
	$item->genre = array($item->genre);

	$mongo->setCollection("items");

	$mongo->insert($item);

	echo (string) $item->_id;
	var_dump($item);
	exit;
});

$app->put('/items/:id', function($id) use($app, $mongo){
	$request = $app->request();

	$item = json_decode($request->getBody());
	$item->genre = array($item->genre);

	$mongo->setCollection("items");

	$mongo->update(array("_id"=>new MongoId($id)), $item);
});

$app->delete('/items/:id', function($id) use($app, $mongo){
	$request = $app->request();

	$item = json_decode($request->getBody());

	$mongo->setCollection("items");

	$mongo->delete(array("_id"=>new MongoId($id)));
});


$app->get("/fb/book/:id",function($id) use($app, $mongo){
	

	$mongo->setCollection("items");
	$data = $mongo->get(array("_id"=>new MongoId($id)));
	if(empty($data)){
		return;
	}
	$data = $data[0];
	$data["app_id"] = AppInfo::appID();
	$data["app_namespace"] = AppInfo::appNamespace();
	$data["url"] = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$data["url_user"] = $app->item_address.$id;
	if(!isset($data['description'])) $data["description"] = "";
	$data['image']= "http://placekitten.com/300/300";
	

	if(preg_match('/^FacebookExternalHit\/.*?/i',$_SERVER['HTTP_USER_AGENT'])){
	  
	}else {
	  // if(getenv("APP_STAGE") == "production")  
	  // 	header("Location: ". $app->item_address.$id);
	}

	$app->render('template-opengraph-book.tpl', $data);
});

$app->get("/fb/magazine/:id",function(){
$mongo->setCollection("items");
	$data = $mongo->get(array("_id"=>new MongoId($id)));
	if(empty($data)){
		return;
	}
	$data = $data[0];
	$data["app_id"] = AppInfo::appID();
	$data["app_namespace"] = AppInfo::appNamespace();
	$data["url"] = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$data["url_user"] = $app->item_address.$id;
	if(!isset($data['description'])) $data["description"] = "";
	$data['image']= "http://placekitten.com/300/300";
	

	if(preg_match('/^FacebookExternalHit\/.*?/i',$_SERVER['HTTP_USER_AGENT'])){
	  
	}else {
	  // if(getenv("APP_STAGE") == "production")  
	  // 	header("Location: ". $app->item_address.$id);
	}

	$app->render('template-opengraph-magazine.tpl', $data);
});



$app->run();
