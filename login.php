<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once('AppInfo.php');

require_once('sdk/src/facebook.php');

try{
  $facebook = new Facebook(array(
    'appId'  => AppInfo::appID(),
    'secret' => AppInfo::appSecret(),
    'sharedSession' => true,
    'trustForwarded' => true
  ));
}catch(Exception $e){
  var_dump($e);
}

$user_id = $facebook->getUser();
//var_dump($user_id);
//var_dump($_SERVER);
header("Location: http://".$_SERVER['HTTP_HOST']."#page1");