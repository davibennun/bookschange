<?php

require_once('AppInfo.php');
require_once('sdk/src/facebook.php');

$facebook = new Facebook(array(
  'appId'  => AppInfo::appID(),
  'secret' => AppInfo::appSecret(),
  'sharedSession' => true,
  'trustForwarded' => true,
));

setcookie('fbs_'.$facebook->getAppId(), '', time()-100, '/', 'domain.com');
session_destroy();
header('Location: /');