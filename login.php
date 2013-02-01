<?php

ini_set("display_errors", 1);

require_once('AppInfo.php');

require_once('sdk/src/facebook.php');

try{
  $facebook = new Facebook(array(
    'appId'  => AppInfo::appID(),
    'secret' => AppInfo::appSecret(),
  ));

}catch(Exception $e){
  var_dump($e);
  echo "Erro init: $e";
  return;
}

try{
	$user_id = $facebook->getUser();
}catch(Exception $e){
	var_dump($e);
	echo "Erro no getUser: $e";
	return;
}

var_dump($user_id);
echo $_REQUEST["code"];
//header("Location: http://".$_SERVER['HTTP_HOST']);