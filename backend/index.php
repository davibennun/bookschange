<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

require 'Slim/Slim.php';
require 'Slim/Middleware.php';
require 'AuthMiddleware.class.php';

require 'MongoWrapper.class.php';


\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
$app->add(new \AuthMiddleware());

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


$app->get('/items/:limit', function($limit) use($app, $mongo){
	$mongo->setCollection("items");

	$result = $mongo->get(array(),$limit);

	echo json_encode($result);
});

$app->get('/items/recommendations/:limit', function($limit) use($app, $mongo){
	$mongo->setCollection("usuarios");
	$user = $mongo->find(array("fb_id"=>$app->fb_id));


	
	$mongo->setColection("items");
	
	//Get in genres collection what we have in user genres
	$crietria = array("genre"=>array("$in"=>$user['genre']));

	$result = $mongo->get($criteria ,$limit);
});

$app->get('/items/search/:query', function($querfy) use($app, $mongo){
	$mongo->setCollection("items");
	
	$crietria = array('$or'=>array("genre"=>new MongoRegex("/$search_query/i"),"title"=>new MongoRegex("/$search_query/i")));

	$result = $mongo->get($criteria, 100);
});

$app->post('/items/',  function() use($app, $mongo){
	$request = $app->request();

	$item = json_decode($request->getBody());
	
	//customize item
	$item->fb_id = $app->fb_id;
	$item->genre = array($item->genre);

	$mongo->setCollection("items");

	$mongo->insert($item);

	
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


$app->run();
