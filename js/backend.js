var backend = (function($){

          var items = window.bookschange.items || [];
          var recommendations = window.bookschange.recommendations || [];
          var notifications = window.bookschange.notifications || [];

          var graphUrl = "http://"+window.location.host+"/backend/fb";

          var limit = 15;

          if(window.location.hostname.indexOf("localhost") >= 0){
            var urls = {
              "recommendations": "/backend/items/recommendations/{1}",
              "items":"/backend/items/{1}",
              "itemsSearch": "/bookschange/shielded-sierra-1174/backend/items/search/{1}",
              "itemsAdd":"/backend/items/",
              "itemsUpdate":"/backend/items/{1}",
              "itemsDelte":"/backend/items/{1}",
              "notifications":"/backend/notifications/",
              "notificationsAdd":"/bookschange/shielded-sierra-1174/backend/notifications/",
              "notificationsUpdate":"/backend/notifications/{1}",
              "notificationsDelete":"/backend/notifications/{1}"
            };
          }else{
            var urls = {
              "recommendations": "/backend/items/recommendations/{1}",
              "items":"/backend/items/{1}",
              "itemsSearch": "/backend/items/search/{1}",
              "itemsAdd":"/backend/items/",
              "itemsUpdate":"/backend/items/{1}",
              "itemsDelte":"/backend/items/{1}",
              "notifications":"/backend/notifications/",
              "notificationsAdd":"/backend/notifications/",
              "notificationsUpdate":"/backend/notifications/{1}",
              "notificationsDelete":"/backend/notifications/{1}"
            };
          }
          

          var collections = {


            recommendations : {
              get: function(id){
                if(id){
                  var result = _.select(recommendations,function(item){
                    return item.id == id; 
                  });
                  return result;
                }else{
                  return recommendations;
                }
                
              },

              fetch:function(id,callback){
                var url = urls.items.replace("{1}",id);

                $.get(url, function(data){
                    callback.apply(null, JSON.parse(data));
                },this).error(function(){console.log("Unable to reach backend")});
              }

            },

            items : {

              get: function(id){
                if(id){
                  var result = _.select(items,function(item){
                    return item.id == id; 
                  });
                  return result;
                }else{
                  return items;
                }
                
              },

              fetch: function(id, callback){
                var url = urls.items.replace("{1}",[id]);

                $.get(url, function(data){
                    callback.apply(null, data);
                },this).error(function(){console.log("Unable to reach backend")});
              },

              search:function(query,callback){
                query = query.toLocaleLowerCase();
                var results = [];
                var url = urls.itemsSearch.replace("{1}",query);

                $.ajax({
                    type: 'GET',
                    url: url,
                    complete: function (xhr, status) {
                     
                     console.log(JSON.parse(xhr.responseText));
                     callback.call(null, JSON.parse(xhr.responseText)); 

                     },//console.log(xhr); },//this.results.push(JSON.parse(xhr.responseText)); },
                    error: function(jqXHR, textStatus, errorThrown){
                        console.log("Unable to reach backend");
                    }
                });

                // for (var i in items){
                //   var item = items[i];

                //   if(item['title'].toLocaleLowerCase().indexOf(query) >= 0){
                //     results.push(item);
                //     break;
                //   }
                    

                //   for(var j in item['genre']){
                //     var text = item['genre'][j].toLocaleLowerCase();
                //     if(text.indexOf(query) >= 0){
                //       results.push(item);
                //     }
                //   }
                // }

                return results;


              },

              add: function(item){
                item.fb_id = window.fb_id;
                
                
                $.ajax({
                    type: 'POST',
                    url: urls.itemsAdd,
                    data: JSON.stringify(item),
                    success: function(data){
                      var url = graphUrl+"/"+item.type+"/"+data;

                      items.push(item);
                      $.dynamic_popup(item.type+' added.');
                      FB.api(
                      '/me/bookschange:donate?book='+url,
                      'post',
                      function(response) {
                         if (!response || response.error) {
                            console.log(response);
                            console.log('Error occured');
                         } else {
                            console.log('Cook was successful! Action ID: ' + response.id);
                         }
                      });
                    },
                    error: function(jqXHR, textStatus, errorThrown){
                        console.log("Unable to reach backend");
                    }
                });


              },

              update: function(id, newItem){
                var url = tokenReplace(urls.itemsUpdate,[id]);
                $.ajax({
                    type: 'PUT',
                    contentType: 'application/json',
                    url: url,
                    dataType: "json",
                    data: newItem,
                    success: function(data){
                      _.detect(items,function(item){
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
                var url = tokenReplace(urls.itemsDelete,[id]);
                $.ajax({
                    type: 'DELETE',
                    url: url,
                    success: function(data){
                      _.detect(items,function(key, item){
                        if(item.id == id){
                          removeValue(items, key);
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
                return notifications;
              },

              fetch: function(limit){
                limit = limit || limit;
                var url = tokenReplace(urls.notifications,[limit]);

                $.get(url, item, function(data){
                    notifications = JSON.parse(data);
                },this).error(function(){console.log("Unable to reach backend")});
              },

              add: function(notification){
                var url = urls.notificationsAdd;
                $.post(url ,notification,function(data){
                    notifications.push(notification);
                    $.dynamic_popup('Notification added.');
                }).error(function(){console.log("Unable to reach backend")});


              },

              update: function(id,newNotification){
                var url = tokenReplace(urls.notificationsUpdate,[id]);
                $.ajax({
                    type: 'PUT',
                    contentType: 'application/json',
                    url: url,
                    dataType: "json",
                    data: newNotification,
                    success: function(data){
                      _.detect(notifications,function(notification){
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
                var url = tokenReplace(urls.notificationsDelete,[id]);
                $.ajax({
                    type: 'DELETE',
                    url: url,
                    success: function(data){
                      _.detect(notifications,function(key, notification){
                        if(notification.id == id){
                          removeValue(notifications, key);
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


          return collections;


        })($);