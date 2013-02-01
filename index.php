<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);


//8:30

/**
 * This sample app is provided to kickstart your experience using Facebook's
 * resources for developers.  This sample app provides examples of several
 * key concepts, including authentication, the Graph API, and FQL (Facebook
 * Query Language). Please visit the docs at 'developers.facebook.com/docs'
 * to learn more about the resources available to you
 */

// Provides access to app specific values such as your app id and app secret.
// Defined in 'AppInfo.php'

require_once('AppInfo.php');

// Requires Mongo DB class wrapper
require_once('backend/MongoWrapper.class.php');

// Enforce https on production
if (substr(AppInfo::getUrl(), 0, 8) != 'https://' && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
//  header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
//  exit();
}

// This provides access to helper functions defined in 'utils.php'
require_once('utils.php');

if(getenv("APP_STAGE") == "production"){  
/*****************************************************************************
 *
 * The content below provides examples of how to fetch Facebook data using the
 * Graph API and FQL.  It uses the helper functions defined in 'utils.php' to
 * do so.  You should change this section so that it prepares all of the
 * information that you want to display to the user.
 *
 ****************************************************************************/

require_once('sdk/src/facebook.php');

try{
  $facebook = new Facebook(array(
    'appId'  => AppInfo::appID(),
    'secret' => AppInfo::appSecret(),
    'sharedSession' => true,
    'trustForwarded' => true,
  ));
}catch(Exception $e){
  var_dump($e);
}

$user_id = $facebook->getUser();

if(isset($_GET['state'])){
  //header('Location: '.$_SERVER['PHP_SELF']);
  //var_dump($_SERVER);

}

if ($user_id) {
  try {
    // Fetch the viewer's basic information
    $basic = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    // If the call fails we check if we still have a user. The user will be
    // cleared if the error is because of an invalid accesstoken
    if (!$facebook->getUser()) {
      header('Location: '. AppInfo::getUrl($_SERVER['REQUEST_URI']));
      exit();
    }
  }



  

  // $mongo->setCollection("users");
  // $user_data = $mongo->get(array("uid"=>$basic["uid"]));

  // if(empty($user_data)){
  //   //register it on db
  // }else{
  //   //set it on session
  // }


  // This fetches some things that you like . 'limit=*" only returns * values.
  // To see the format of the data you are retrieving, use the "Graph API
  // Explorer" which is at https://developers.facebook.com/tools/explorer/
  $likes = idx($facebook->api('/me/likes?limit=4'), 'data', array());

  // This fetches 4 of your friends.
  $friends = idx($facebook->api('/me/friends?limit=4'), 'data', array());

  // And this returns 16 of your photos.
  $photos = idx($facebook->api('/me/photos?limit=16'), 'data', array());

  // Here is an example of a FQL call that fetches all of your friends that are
  // using this app
  $app_using_friends = $facebook->api(array(
    'method' => 'fql.query',
    'query' => 'SELECT uid, name FROM user WHERE uid IN(SELECT uid2 FROM friend WHERE uid1 = me()) AND is_app_user = 1'
  ));
}

// Fetch the basic info of the app that they are using
$app_info = $facebook->api('/'. AppInfo::appID());

$app_name = idx($app_info, 'name', '');

$logoutUrl = $facebook->getLogoutUrl();//array( 'next' => ($_SERVER['HTTP_HOST'].'/logout.php') ));

}else{

  $basic = array();
  $user_id = "123456";

  $logoutUrl = "#";
  

}



//Fetch items
  $mongo = new \MongoWrapper\MongoWrapper();
  $mongo->setDatabase("bookschange");
  $mongo->setCollection("items");
  $items = $mongo->get(array("fb_id"=>$user_id),10);
  

  //Fetch recommendations
  $mongo->setCollection("usuarios");
  $user = $mongo->get(array("fb_id"=>$user_id));

  isset($user[0]) ? $user = $user[0] : "";

  $mongo->setCollection("items");

  isset($user['genres']) ? $recommendations = $mongo->get(array("genre"=>array('$in'=>$user['genres'])),10) : $recommendations = array();

  //Fetch notifications
  //Fetch recommendations
  $mongo->setCollection("notifications");
  $notifications = $mongo->get(array("fb_id"=>$user_id));

  

?><!DOCTYPE html>
<html xmlns:fb="http://ogp.me/ns/fb#" lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  <title>BooksChange</title>
  <link rel="stylesheet" href="https://ajax.aspnetcdn.com/ajax/jquery.mobile/1.2.0/jquery.mobile-1.2.0.min.css" />
  <link rel="stylesheet" href="my.css" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
  <script src="https://ajax.aspnetcdn.com/ajax/jquery.mobile/1.2.0/jquery.mobile-1.2.0.min.js"></script>
  <script type="text/javascript" src="js/jquery.mobile.router.min.js"></script>
  <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.3/underscore-min.js"></script>
  <script type="text/javascript" src="js/jquery.mobile.dynamic.popup.min.js"></script>
  <script type="text/javascript" src="js/jquery.textchange.js"></script>
  <script type="text/javascript" src="js/pages.js"></script>
  <script type="text/javascript" src="js/install.js"></script>
  <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/mustache.js/0.7.0/mustache.min.js"></script>
  <script src="my.js"></script>
<?php if(getenv("APP_STAGE") == "production"){   ?>
  <meta property="og:title" content="<?php echo he($app_name); ?>" />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="<?php echo AppInfo::getUrl(); ?>" />
  <meta property="og:image" content="<?php echo AppInfo::getUrl('/logo.png'); ?>" />
  <meta property="og:site_name" content="<?php echo he($app_name); ?>" />
  <meta property="og:description" content="Books Change" />
  <meta property="fb:app_id" content="<?php echo AppInfo::appID(); ?>" />
<?php }?>


  <style type="text/css">

    body {
    background: url("images/fundo_inicio.jpg");
    background-repeat:repeat-y;
    background-position:center center;
    background-attachment:scroll;
    background-size:100% 100%;
}
.ui-page {
    background: transparent;
}
.ui-content{
    background: transparent;
}

    .ui-popup-container {
      z-index: 1100;
      display: inline-block;
      position: absolute;
      padding: 0;
      outline: 0;
    }

    .ui-popup-hidden {
      top: -99999px;
      left: -9999px;
    }

    #login-box{
      position:fixed;
      top: 50%;
      left: 50%;
      width:30em;
      height:18em;
      margin-top: -9em; /*set to a negative number 1/2 of your height*/
      margin-left: -15em; /*set to a negative number 1/2 of your width*/
      border: 1px solid #ccc;
      background-color: #f3f3f3;
    }

  </style>

  <script type="text/javascript">

    window.bookschange = {};

    window.bookschange.items = <?php echo json_encode($items); ?>;
    window.bookschange.recommendations = <?php echo json_encode($recommendations); ?>;
    window.bookschange.notifications = <?php echo json_encode($notifications); ?>;

    window.fb_id = "<?php echo $user_id; ?>";
  </script>

  <script type="text/html" id="template-itemsList">
    <ul data-role="listview" data-divider-theme="b" data-inset="true">
        {{#data}}
        <li data-theme="c">
          <a href="#page2?item_id={{id}}" data-transition="slide">
            {{title}}
          </a>
        </li>
        {{/data}}
    </ul>
  </script>

  <script type="text/html" id="template-itemInfo"> 
    <h2>
        {{title}}
      </h2>
      
      <div>
          {{description}}
      </div>
      <div>
          <a href="" data-transition="slide">
              <a href="{{profile_link}}">Contact owner</a>
          </a>
      </div>
  </script>

  <script type="text/html" id="template-notificationsList">
  <ul data-role="listview" data-divider-theme="b" data-inset="true">
  {{#data}}
      <li data-theme="c">
            {{title}}
        </li>
  {{/data}}
  </ul>
  </script>

</head>
<body>
  <div id="fb-root"></div>
  <script type="text/javascript">
      window.fbAsyncInit = function() {
        FB.init({
          appId      : '<?php echo AppInfo::appID(); ?>', // App ID
          channelUrl : '//<?php echo $_SERVER["HTTP_HOST"]; ?>/channel.html', // Channel File
          status     : true, // check login status
          cookie     : true, // enable cookies to allow the server to access the session
          xfbml      : true // parse XFBML
        });

        FB.Event.subscribe('auth.login', function(response) {
          // We want to reload the page now so PHP can read the cookie that the
          // Javascript SDK sat. But we don't want to use
          // window.location.reload() because if this is in a canvas there was a
          // post made to this page and a reload will trigger a message to the
          // user asking if they want to send data again.
          //window.location = window.location;
          window.location.reload();
        });

      }
      
      
      

      var templates = {
        itemsList : Mustache.compile($("#template-itemsList").html()),
        itemInfo: Mustache.compile($("#template-itemInfo").html()),
        notificationsList : Mustache.compile($("#template-notificationsList").html())
      }

        var app_init = function(){

          //todo o controler tem que iniciar

          var controller = new Controller("1");
          // FB.api('/me', function(response) {
            
          // });  

          $(".install-app").click(function(e){
            console.log("test");
            


            mozilla = "http://"+window.location.hostname + '/manifest.webapp';
            console.log(mozilla);
            mozillaInstall = function () {
                var installRequest = navigator.mozApps.install(mozilla);

                installRequest.onsuccess = function (data) {
                    console.log("success");
                };

                installRequest.onerror = function (err) {
                  console.log("error");
                };
            };

            mozillaInstall();



          });

        
        var timeout;
        $('#search-input').bind('keyup', function (e) {
          if(e.which == 13) {
              controller.go("8");
          }

          e.preventDefault();
        });

        $("#ui-input-clear").live("click",function(){
          $("#search-input").val("");
        });

          // ------ROUTER
          $(document).bind( "pagebeforechange", function( e, data ) {

            
            if ( typeof data.toPage === "string" ) {

              //Parse url
              var u = $.mobile.path.parseUrl( data.toPage );
              
              //get pageID
              var page_id = u.hash.replace("#page","").replace(/\?(.*)/g,"");

              //Dispach
              controller.go(page_id,e,data);

                            

            }
          });

          if(window.location.hash.length<=0){
            controller.go("1");
          }else if(window.location.hash.indexOf("itemId")){
            var data = {};
            data.toPage = window.location.href;

            controller.go("2",{},data);
          }


          

          $("#donate_form").submit(function(e){
              e.preventDefault();

              backend.items.add(formatFormData($(this).serializeArray()));
              
              
          });

          $("#notification_submit").submit(function(e){
             backend.notifications.add(formatFormData($(this).serializeArray())); 
          });

          //1 broser
          //6 add
          //7 items
          //5 notify

          

        };

        
        

        
        

        // FB.Canvas.setAutoGrow();

        // FB.getLoginStatus(function(response) {
        //   if (response.authResponse) {
        //     app_init();
        //   } else {
        //     console.log("unLOGADO");
        //   }
        // });

        

        $(function(){
         
          app_init();

          

        });





     

      function formatFormData(data){
          var formatedData = {};

          for(var key in data){
            formatedData[data[key]["name"]] = data[key]["value"]; 
          }

          return formatedData;
        }

        function tokenReplace(string, tokens){  
          // for (var i = 0; i < tokens.length; i++) {
          //     string.replace(new RegExp("\{"+(i+1)+"\}","g"),tokens[i]);
          // }
          string.replace("{1}",tokens); //desespero
          return string;
        }

        function removeValue(arr, value) {
            for(var i = 0; i < arr.length; i++) {
                if(arr[1] === value) {
                    return arr.splice(i, 1);
                }
            }
        }

        function updateElement(){

        }

        function deleteElement(){

        }

      //Load the SDK Asynchronously
      (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/all.js";
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));
    </script>


  <?php if (isset($basic)) { ?>




  <!-- Home -->
  <div data-role="page" id="page1">



      <div data-theme="a" data-role="header">
        
  <a data-role="button" href="#popupMenu" data-rel="popup" data-icon="gear" data-iconpos="notext" class="ui-btn-right codiqa-control ui-btn ui-shadow ui-btn-corner-all ui-btn-icon-notext ui-btn-active ui-btn-up-a" data-cid="button8" data-corners="true" data-shadow="true" data-iconshadow="true" data-iconsize="18" data-wrapperels="span" data-theme="a" title="
    Actions
  "><span class="ui-btn-inner ui-btn-corner-all"><span class="ui-btn-text">
    Actions
  </span><span class="ui-icon ui-icon-gear ui-icon-shadow ui-iconsize-18">&nbsp;</span></span></a>
          <h3>
              Books change
          </h3>
      
      </div>
      <div data-role="content">
          <h3>
              Recomendations
          </h3>
          <div id="page1-content">
          </div>


<div data-role="popup" id="popupMenu" data-theme="a" class="ui-popup ui-body-a ui-overlay-shadow ui-corner-all" aria-disabled="false" data-disabled="false" data-shadow="true" data-corners="true" data-transition="none" data-position-to="origin" data-dismissible="true">
        <ul data-role="listview" data-theme="b" data-inset="true" data-cid="listview5" class="codiqa-control ui-listview ui-listview-inset ui-corner-all ui-shadow">
    
      
        <li data-role="divider" data-theme="a" class="ui-li ui-li-static ui-btn-up-a ui-first-child">Actions</li>
          <li data-theme="b" data-corners="false" data-shadow="false" data-iconshadow="true" data-iconsize="18" data-wrapperels="div" data-icon="arrow-r" data-iconpos="right" class="ui-btn ui-btn-icon-right ui-li-has-arrow ui-li ui-corner-top ui-corner-bottom ui-li-last ui-btn-up-b">
            <div class="ui-btn-inner ui-li ui-corner-top">
              <div class="ui-btn-text">
                <a href="<?php echo $logoutUrl; ?>" data-transition="slide" class="ui-link-inherit">Logout</a>
              </div>
              <span class="ui-icon ui-icon-arrow-r ui-icon-shadow ui-iconsize-18">&nbsp;</span>
            </div>
          </li>
  </ul>
    </div>
    
          <div id="page8-content"></div>
          <form action="">
              <div data-role="fieldcontain">
                  <fieldset data-role="controlgroup">
                      <label for="search-input">
                      </label>
                      <input name="" id="search-input" placeholder="Search by title or genre"
                      value="" type="search">
                  </fieldset>
              </div>
          </form>
      </div>
      <div data-role="tabbar" data-iconpos="top" data-theme="a">
          <ul>
              <li>
                  <a href="#page1" data-transition="slide" data-theme="" data-icon="grid">
                      Browse
                  </a>
              </li>
              <li>
                  <a href="#page6" data-transition="slide" data-theme="" data-icon="plus">
                      Add
                  </a>
              </li>
              <li>
                  <a href="#page7" data-transition="slide" data-theme="" data-icon="star">
                      Items
                  </a>
              </li>
              <li>
                  <a href="#page5" data-transition="slide" data-theme="" data-icon="refresh">
                      Notify
                  </a>
              </li>
          </ul>
      </div>



    


  </div>
  <!-- details -->
  <div data-role="page" id="page2">
      <div data-theme="a" data-role="header">
          <a href="#" class="ui-btn-left ui-btn ui-shadow ui-btn-corner-all ui-btn-icon-left ui-btn-up-a" data-rel="back" data-icon="arrow-l" data-theme="a" data-corners="true" data-shadow="true" data-iconshadow="true" data-wrapperels="span"><span class="ui-btn-inner ui-btn-corner-all"><span class="ui-btn-text">Back</span><span class="ui-icon ui-icon-arrow-l ui-icon-shadow">&nbsp;</span></span></a>
          <a id="item_edit" data-role="button" href="#page2" data-icon="gear" data-iconpos="left"
          class="ui-btn-right">
              -
          </a>
          <a id="item_delete" data-role="button" data-inline="true" data-transition="slide"
          href="#page6" data-icon="minus" data-iconpos="notext" class="ui-btn-right">
          </a>
          <h3>
              Books Change
          </h3>
      </div>
      <div data-role="content" id="item-info-content">
          <!-- Book info -->

      </div>
      <div data-role="tabbar" data-iconpos="top" data-theme="a">
          <ul>
              <li>
                  <a href="#page1" data-transition="slide" data-theme="" data-icon="grid">
                      Browse
                  </a>
              </li>
              <li>
                  <a href="#page6" data-transition="slide" data-theme="" data-icon="plus">
                      Add
                  </a>
              </li>
              <li>
                  <a href="#page7" data-transition="slide" data-theme="" data-icon="star">
                      Items
                  </a>
              </li>
              <li>
                  <a href="#page5" data-transition="slide" data-theme="" data-icon="refresh">
                      Notify
                  </a>
              </li>
          </ul>
      </div>
  </div>
  <!-- choose_genres -->
  <div data-role="page" id="page3">
      <div data-theme="a" data-role="header">
          <h3>
              Books Change
          </h3>
      </div>
      <div data-role="content">
          <h3>
              Choose book genres you like
          </h3>
          <form action="">
              <div id="checkboxes4" data-role="fieldcontain">
                  <fieldset data-role="controlgroup" data-type="vertical" data-mini="true">
                      <legend>
                      </legend>
                      <input id="checkbox8" name="drama" type="checkbox">
                      <label for="checkbox8">
                          Drama
                      </label>
                      <input id="checkbox9" name="fiction" type="checkbox">
                      <label for="checkbox9">
                          Fiction
                      </label>
                      <input id="checkbox10" name="romance" type="checkbox">
                      <label for="checkbox10">
                          Romance
                      </label>
                      <input id="checkbox11" name="manga" type="checkbox">
                      <label for="checkbox11">
                          Manga
                      </label>
                  </fieldset>
              </div>
              <input id="genres_submit" type="submit" value="Submit" data-mini="true">
          </form>
      </div>
      <div data-role="tabbar" data-iconpos="top" data-theme="a">
          <ul>
              <li>
                  <a href="#page1" data-transition="slide" data-theme="" data-icon="grid">
                      Browse
                  </a>
              </li>
              <li>
                  <a href="#page6" data-transition="slide" data-theme="" data-icon="plus">
                      Add
                  </a>
              </li>
              <li>
                  <a href="#page7" data-transition="slide" data-theme="" data-icon="star">
                      Items
                  </a>
              </li>
              <li>
                  <a href="#page5" data-transition="slide" data-theme="" data-icon="refresh">
                      Notify
                  </a>
              </li>
          </ul>
      </div>
  </div>
  <!-- notifications -->
  <div data-role="page" id="page5">
      <div data-theme="a" data-role="header">
          <h3>
              Books Change
          </h3>
      </div>
      <div data-role="content">
          <a id="notifications_add" data-role="button" data-inline="true" data-transition="slide"
          href="#page9">
              Add
          </a>
          <h3>
              Notifications
          </h3>
          <div data-role="collapsible-set" data-content-theme="d" id="page5-content">
              <!-- Info -->
          </div>
      </div>
      <div data-role="tabbar" data-iconpos="top" data-theme="a">
          <ul>
              <li>
                  <a href="#page1" data-transition="slide" data-theme="" data-icon="grid">
                      Browse
                  </a>
              </li>
              <li>
                  <a href="#page6" data-transition="slide" data-theme="" data-icon="plus">
                      Add
                  </a>
              </li>
              <li>
                  <a href="#page7" data-transition="slide" data-theme="" data-icon="star">
                      Items
                  </a>
              </li>
              <li>
                  <a href="#page5" data-transition="slide" data-theme="" data-icon="refresh">
                      Notify
                  </a>
              </li>
          </ul>
      </div>
  </div>
  <!-- Donate -->
  <div data-role="page" id="page6">
      <div data-theme="a" data-role="header">
          <h3>
              Books Change
          </h3>
      </div>
      <div data-role="content">
          <form id="donate_form" action="" method="POST" data-ajax="false">
              <div data-role="fieldcontain">
                  <fieldset data-role="controlgroup" data-type="vertical">
                      <legend>
                          Type
                      </legend>
                      <input id="radio1" name="type" value="book" type="radio">
                      <label for="radio1">
                          Book
                      </label>
                      <input id="radio2" name="type" value="magazine" type="radio">
                      <label for="radio2">
                          Magazine
                      </label>
                  </fieldset>
              </div>
              <div data-role="fieldcontain">
                  <fieldset data-role="controlgroup" data-mini="true">
                      <label for="textinput3">
                          Title
                      </label>
                      <input name="title" id="textinput3" placeholder="" value="" type="text">
                  </fieldset>
              </div>
              <div data-role="fieldcontain">
                  <fieldset data-role="controlgroup">
                      <label for="textinput4">
                          Genre
                      </label>
                      <input name="genre" id="textinput4" placeholder="" value="" type="text">
                  </fieldset>
              </div>
              <div data-role="fieldcontain">
                  <fieldset data-role="controlgroup">
                      <label for="textarea1">
                          Description
                      </label>
                      <textarea name="description" id="textarea1" placeholder=""></textarea>
                  </fieldset>
              </div>
              <input id="donation_submit" type="submit" value="Submit">
          </form>
      </div>
      <div data-role="tabbar" data-iconpos="top" data-theme="a">
          <ul>
              <li>
                  <a href="#page1" data-transition="slide" data-theme="" data-icon="grid">
                      Browse
                  </a>
              </li>
              <li>
                  <a href="#page6" data-transition="slide" data-theme="" data-icon="plus">
                      Add
                  </a>
              </li>
              <li>
                  <a href="#page7" class="page7-class" data-transition="slide" data-theme="" data-icon="star">
                      Items
                  </a>
              </li>
              <li>
                  <a href="#page5" data-transition="slide" data-theme="" data-icon="refresh">
                      Notify
                  </a>
              </li>
          </ul>
      </div>
  </div>
  <!-- Items -->
  <div data-role="page" id="page7">
      <div data-theme="a" data-role="header">
          <h3>
              Books Change
          </h3>
      </div>
      <div data-role="content" id="page7-content">
          
      </div>
      <div data-role="tabbar" data-iconpos="top" data-theme="a">
          <ul>
              <li>
                  <a href="#page1" data-transition="slide" data-theme="" data-icon="grid">
                      Browse
                  </a>
              </li>
              <li>
                  <a href="#page6" data-transition="slide" data-theme="" data-icon="plus">
                      Add
                  </a>
              </li>
              <li>
                  <a href="#page7" data-transition="slide" data-theme="" data-icon="star">
                      Items
                  </a>
              </li>
              <li>
                  <a href="#page5" data-transition="slide" data-theme="" data-icon="refresh">
                      Notify
                  </a>
              </li>
          </ul>
      </div>
  </div>

  <div data-role="page" id="page9">
     <div data-theme="a" data-role="header">
          <h3>
              Books Change
          </h3>
      </div>
    <div data-role="content">
     
      <div data-role="content">
          <form id="notification_form" action="" method="POST" data-ajax="false">
              <div data-role="fieldcontain">
                  <fieldset data-role="controlgroup" data-type="vertical">
                      <legend>
                          Type
                      </legend>
                      <input id="radio1" name="type" value="book" type="radio">
                      <label for="radio1">
                          Book
                      </label>
                      <input id="radio2" name="type" value="magazine" type="radio">
                      <label for="radio2">
                          Magazine
                      </label>
                  </fieldset>
              </div>
              <div data-role="fieldcontain">
                  <fieldset data-role="controlgroup" data-mini="true">
                      <label for="textinput3">
                          Title
                      </label>
                      <input name="title" id="textinput3" placeholder="" value="" type="text">
                  </fieldset>
              </div>
              <div data-role="fieldcontain">
                  <fieldset data-role="controlgroup">
                      <label for="textinput4">
                          Genre
                      </label>
                      <input name="genre" id="textinput4" placeholder="" value="" type="text">
                  </fieldset>
              </div>
              
              <input id="notification_submit" type="submit" value="Submit">
          </form>
      </div>
      <div data-role="tabbar" data-iconpos="top" data-theme="a">
          <ul>
              <li>
                  <a href="#page1" data-transition="slide" data-theme="" data-icon="grid">
                      Browse
                  </a>
              </li>
              <li>
                  <a href="#page6" data-transition="slide" data-theme="" data-icon="plus">
                      Add
                  </a>
              </li>
              <li>
                  <a href="#page7" class="page7-class" data-transition="slide" data-theme="" data-icon="star">
                      Items
                  </a>
              </li>
              <li>
                  <a href="#page5" data-transition="slide" data-theme="" data-icon="refresh">
                      Notify
                  </a>
              </li>
          </ul>
      </div>
    </div>
  </div>


 


   <?php } else { 

    $params = array(
      'scope' => 'user_likes, user_photos, publish_actions',
      //'redirect_uri' => $_SERVER['HTTP_HOST']
    );

    $loginUrl = $facebook->getLoginUrl($params);

  ?>
      
      <div id="login-box">
        <h1>Sign in with facebook</h1>
        <a href="<?php echo $loginUrl; ?>">Entrar com Facebook</a>
        <button class="install-app">Install App</button>
      </div>

    <?php } ?>

    
    <script type="text/javascript" src="js/backend.js"></script>
</body>
</html>
