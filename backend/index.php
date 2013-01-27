<?php
error_reporting(E_ALL);

require 'Slim/Slim.php';
require 'MongoWrapper.class.php';


\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();


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
	$mongo->setColection("items");

	//todo get user liked genres
	$genres = array("romance","fiction");
	$crietria = array(array("genres"=>$genres));

	$result = $mongo->get($criteria ,$limit);
});

$app->get('/items/search/:query', function($query) use($app, $mongo){
	$mongo->setColection("items");
	
	$crietria = array("$or"=>array("genre"=>new MongoRegex("/$search_query/i"),"title"=>new MongoRegex("/$search_query/i")));

	$result = $mongo->get($criteria, 100);
});

$app->post('/items/',  function() use($app, $mongo){
	$request = $app->request();

	$item = json_decode($request->getBody());

	$mongo->setCollection("items");

	$mongo->insert($item);
});

$app->put('/items/:id', function($id) use($app, $mongo){
	$request = $app->request();

	$item = json_decode($request->getBody());

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
