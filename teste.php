<html>
<body>

<?php

require_once('AppInfo.php');
require_once('utils.php');
require_once('sdk/src/facebook.php');

$facebook = new Facebook(array(
    'appId'  => AppInfo::appID(),
    'secret' => AppInfo::appSecret(),
    'sharedSession' => true,
    'trustForwarded' => true,
    'cookie' => true
));

$user_id = $facebook->getUser();

if (!$user_id) {
    $params = array(
      'scope' => 'user_likes, user_photos, publish_actions',
      //'redirect_uri' => "http://".$_SERVER['HTTP_HOST']."/login.php"
    );

    $loginUrl = $facebook->getLoginUrl($params);
    echo "Login here: <a href='".$loginUrl."'>yep here</a>";
} else {
	try {
		$profile = $facebook->api('/me', 'GET');
		echo "Name is: ".$user_profile['name'];
	} catch(FacebookApiException $e) {
	    $loginUrl = $facebook->getLoginUrl($params);
    	echo "Login here because of exception: <a href='".$loginUrl."'>yep here</a> ".$e->getMessage();
	}
	echo "Your user_id is: ".$user_id;
}

?>

</body>
</html>