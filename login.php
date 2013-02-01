<?php

require_once('AppInfo.php');

try{
  $facebook = new Facebook(array(
    'appId'  => AppInfo::appID(),
    'secret' => AppInfo::appSecret(),
    'sharedSession' => true,
    'trustForwarded' => true,
    'cookie'=>true
  ));
}catch(Exception $e){
  var_dump($e);
}

$user_id = $facebook->getUser();
var_dump($user_id);

//header("Location: http://".$_SERVER['HTTP_HOST']);