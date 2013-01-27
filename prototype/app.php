<?php


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

require_once('../AppInfo.php');

// Enforce https on production
if (substr(AppInfo::getUrl(), 0, 8) != 'https://' && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
//  header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
//  exit();
}

// This provides access to helper functions defined in 'utils.php'
require_once('../utils.php');


/*****************************************************************************
 *
 * The content below provides examples of how to fetch Facebook data using the
 * Graph API and FQL.  It uses the helper functions defined in 'utils.php' to
 * do so.  You should change this section so that it prepares all of the
 * information that you want to display to the user.
 *
 ****************************************************************************/

require_once('../sdk/src/facebook.php');

$facebook = new Facebook(array(
  'appId'  => AppInfo::appID(),
  'secret' => AppInfo::appSecret(),
  'sharedSession' => true,
  'trustForwarded' => true,
));

$user_id = $facebook->getUser();
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
  <script src="my.js"></script>

  <meta property="og:title" content="<?php echo he($app_name); ?>" />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="<?php echo AppInfo::getUrl(); ?>" />
  <meta property="og:image" content="<?php echo AppInfo::getUrl('/logo.png'); ?>" />
  <meta property="og:site_name" content="<?php echo he($app_name); ?>" />
  <meta property="og:description" content="Books Change" />
  <meta property="fb:app_id" content="<?php echo AppInfo::appID(); ?>" />

  <!-- User-generated css -->
  <style>
    
  </style>

  <script type="text/html" id="template-itemsList">
    <ul data-role="listview" data-divider-theme="b" data-inset="true">
        <li data-role="list-divider" role="heading">
            My items
        </li>
        {{#data}}
        <li data-theme="c">
          <a href="#page2" data-transition="slide">
            {{title}}
          </a>
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

        var backend = (function($){

          var items = [{},{},{}];
          var recommendations = [{},{}];
          var notifications = [{}, {}, {}];

          var limit = 15;

          var urls = {
            "recommendations": "backend/items/recommendations/{1}",
            "items":"/backend/items/{1}",
            "itemsSearch": "/items/search/{1}",
            "itemsAdd":"/backend/items/",
            "itemsUpdate":"/backend/items/{1}",
            "itemsDelte":"/backend/items/{1}",
            "notifications":"/backend/notifications/",
            "notificationsAdd":"/backend/notifications/",
            "notificationsUpdate":"/backend/notifications/{1}",
            "notificationsDelete":"/backend/notifications/{1}"
          };

          var methods = {

            recommendations : {
              get:function(){
                return this.recommendations;
              },

              fetch:function(limit){
                limit = limit || this.limit;
                var url = tokenReplace(this.urls,recommendations,[limit]);

                $.get(url, item, function(data){
                    this.recommendations = JSON.parse(data);
                },this).error(function(){console.log("Unable to reach backend")});
              }

            },

            items : {

              get: function(){
                return this.items;
              },

              fetch: function(limit){
                limit = limit || this.limit;
                var url = tokenReplace(this.urls.recommendations,[limit]);

                $.get(url, item, function(data){
                    this.items = JSON.parse(data);
                },this).error(function(){console.log("Unable to reach backend")});
              },

              add: function(item){
                $.post(this.urls.itemsAdd,item,function(data){
                    this.items.push(item);
                }).error(function(){console.log("Unable to reach backend")});
              },

              update: function(id, newItem){
                var url = tokenReplace(this.urls.itemsUpdate,[id]);
                $.ajax({
                    type: 'PUT',
                    contentType: 'application/json',
                    url: url,
                    dataType: "json",
                    data: newItem,
                    success: function(data){
                      _.detect(this.items,function(item){
                        if(item.id == id){
                          item = newItem; //if this does not work try notification[key] = new Notification instead
                          return true;
                        }
                      });
                    },
                    error: function(jqXHR, textStatus, errorThrown){
                        console.log("Unable to reach backend");
                    }
                });
              },

              delete: function(id){
                var url = tokenReplace(this.urls.itemsDelete,[id]);
                $.ajax({
                    type: 'DELETE',
                    url: url,
                    success: function(data){
                      _.detect(this.items,function(key, item){
                        if(item.id == id){
                          removeValue(this.items, key);
                          return true;
                        }
                      });
                    },
                    error: function(jqXHR, textStatus, errorThrown){
                        console.log("Unable to reach backend");
                    }
                });
                
              }
            
            },    

            notifications : {

              get: function(){
                return this.notifications;
              },

              fetch: function(limit){
                limit = limit || this.limit;
                var url = tokenReplace(this.urls.notifications,[limit]);

                $.get(url, item, function(data){
                    this.notifications = JSON.parse(data);
                },this).error(function(){console.log("Unable to reach backend")});
              },

              add: function(notification){
                $.post(this.urls.notificationAdd,notification,function(data){
                    this.notifications.push(notification);
                }).error(function(){console.log("Unable to reach backend")});
              },

              update: function(id,newNotification){
                var url = tokenReplace(this.urls.notificationsUpdate,[id]);
                $.ajax({
                    type: 'PUT',
                    contentType: 'application/json',
                    url: url,
                    dataType: "json",
                    data: newNotification,
                    success: function(data){
                      _.detect(this.notifications,function(notification){
                        if(notification.id == id){
                          notification = newNotification; //if this does not work try notification[key] = new Notification instead
                          return true;
                        }
                      });
                    },
                    error: function(jqXHR, textStatus, errorThrown){
                        console.log("Unable to reach backend");
                    }
                });
              },

              delete: function(id){
                var url = tokenReplace(this.urls.notificationsDelete,[id]);
                $.ajax({
                    type: 'DELETE',
                    url: url,
                    success: function(data){
                      _.detect(this.notifications,function(key, notification){
                        if(notification.id == id){
                          removeValue(this.notifications, key);
                          return true;
                        }
                      });
                    },
                    error: function(jqXHR, textStatus, errorThrown){
                        console.log("Unable to reach backend");
                    }
                });

              }

            }

          };


          return methods;


        })($);

        var app_init = function(){

          // FB.api('/me', function(response) {
            
          // });  

          //TEMPLATES
          var templateItemsList = Mustache.compile($("#template-itemsList").html());

          $("#donation_submit").click(function(e){
              e.preventDefault();

              backend.items.add(formatFormData($(this).serializeArray()));
              
              console.log("donation submit");
          });

          $("#notification_submit").submit(function(e){
             backend.notifications.add(formatFormData($(this).serializeArray())); 
          });

          //1 broser
          //6 add
          //7 items
          //5 notify

          // BROWSE PAGE
          $('#page1').live( 'pageinit', function(){
            
          });
          
          // notify PAGE
          $('#page5').live( 'pageinit', function(){

          });
          // items PAGE
          $('#page7').live( 'pageinit', function(){
            $("#page7-content").html(templateItemsList({data:backend.items.get()}));
          });

        };

        
        

        // Listen to the auth.login which will be called when the user logs in
        // using the Login button
        FB.Event.subscribe('auth.login', function(response) {
          // We want to reload the page now so PHP can read the cookie that the
          // Javascript SDK sat. But we don't want to use
          // window.location.reload() because if this is in a canvas there was a
          // post made to this page and a reload will trigger a message to the
          // user asking if they want to send data again.
          window.location = window.location;
        });

        FB.Canvas.setAutoGrow();

        FB.getLoginStatus(function(response) {
          if (response.authResponse) {
            app_init();
          } else {
            console.log("unLOGADO");
          }
        });


      };

      function formatFormData(data){
          var formatedData = {};

          for(var key in data){
            formatedData[data[key]["name"]] = data[key]["value"]; 
          }

          return formatedData;
        }

        function tokenReplace(string, tokens){  
          for (var i = 0; i < tokens.length; i++) {
              string.replace(new RegExp("{"+i+"}","g"),tokens[i]);
          }
          return string
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

      // Load the SDK Asynchronously
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
          <h3>
              Books change
          </h3>
      </div>
      <div data-role="content">
          <h3>
              Recomendations
          </h3>
          <div class="ui-grid-b">
              <div class="ui-block-a">
              </div>
              <div class="ui-block-b">
              </div>
              <div class="ui-block-c">
              </div>
              <div class="ui-block-a">
              </div>
              <div class="ui-block-b">
              </div>
              <div class="ui-block-c">
              </div>
              <div class="ui-block-a">
              </div>
              <div class="ui-block-b">
              </div>
              <div class="ui-block-c">
              </div>
              <div class="ui-block-a">
              </div>
              <div class="ui-block-b">
              </div>
              <div class="ui-block-c">
              </div>
          </div>
          <form action="">
              <div data-role="fieldcontain">
                  <fieldset data-role="controlgroup">
                      <label for="search_input">
                      </label>
                      <input name="" id="search_input" placeholder="Search by title or genre"
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
                  <a href="#page5" data-transition="fade" data-theme="" data-icon="refresh">
                      Notify
                  </a>
              </li>
          </ul>
      </div>
  </div>
  <!-- details -->
  <div data-role="page" id="page2">
      <div data-theme="a" data-role="header">
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
      <div data-role="content">
          <h2>
              Item title
          </h2>
          <div style="width: 288px; height: 100px; position: relative; background-color: #fbfbfb; border: 1px solid #b8b8b8;">
              <img src="http://codiqa.com/static/images/v2/image.png" alt="image" style="position: absolute; top: 50%; left: 50%; margin-left: -16px; margin-top: -18px">
          </div>
          <div class="ui-grid-c">
              <div class="ui-block-a">
              </div>
              <div class="ui-block-b">
              </div>
              <div class="ui-block-c">
              </div>
              <div class="ui-block-d">
              </div>
          </div>
          <div>
              <p>
                  <b>
                      Enter content here...
                  </b>
              </p>
              <p>
                  <b>
                      <b>
                          Enter content here...
                      </b>
                  </b>
              </p>
              <p>
                  <b>
                      <b>
                          Enter content here...
                      </b>
                  </b>
              </p>
              <p>
                  <b>
                      <b>
                          Enter content here...
                      </b>
                  </b>
              </p>
              <p>
                  <br>
              </p>
          </div>
          <div>
              <a href="" data-transition="fade">
                  Contact owner
              </a>
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
                  <a href="#page5" data-transition="fade" data-theme="" data-icon="refresh">
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
                  <a href="#page5" data-transition="fade" data-theme="" data-icon="refresh">
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
          href="#page5">
              Add
          </a>
          <h3>
              Notifications
          </h3>
          <div data-role="collapsible-set" data-content-theme="d">
              <div data-role="collapsible" data-collapsed="false">
                  <h3>
                      Book 1
                  </h3>
              </div>
              <div data-role="collapsible" data-collapsed="false">
                  <h3>
                      Book 2
                  </h3>
              </div>
              <div data-role="collapsible" data-collapsed="false">
                  <h3>
                      Book 3
                  </h3>
              </div>
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
                  <a href="#page5" data-transition="fade" data-theme="" data-icon="refresh">
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
          <form id="donate_form" action="" method="POST">
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
                          ISBN
                      </label>
                      <input name="isbn" id="textinput4" placeholder="" value="" type="text">
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
              <input id="donation_submit" type="button" value="Submit">
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
                  <a href="#page5" data-transition="fade" data-theme="" data-icon="refresh">
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
                  <a href="#page5" data-transition="fade" data-theme="" data-icon="refresh">
                      Notify
                  </a>
              </li>
          </ul>
      </div>
  </div>

   <?php } else { ?>
      
      <div>
        <h1>Entre com o facebook</h1>
        <div class="fb-login-button" data-scope="user_likes,user_photos"></div>
      </div>

    <?php } ?>

    <<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/mustache.js/0.7.0/mustache.min.js"></script>
</body>
</html>
