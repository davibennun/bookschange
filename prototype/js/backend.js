var backend = (function($){

          var items = window.bookschange.items || [];
          var recommendations = window.bookschange.recommendations || [];
          var notifications = window.bookschange.notifications || [];

          var limit = 15;

          var urls = {
            "recommendations": "http://localhost/bookschange/shielded-sierra-1174/backend/items/recommendations/{1}",
            "items":"http://localhost/bookschange/shielded-sierra-1174/backend/items/{1}",
            "itemsSearch": "http://localhost/bookschange/shielded-sierra-1174/items/search/{1}",
            "itemsAdd":"http://localhost/bookschange/shielded-sierra-1174/backend/items/",
            "itemsUpdate":"http://localhost/bookschange/shielded-sierra-1174/backend/items/{1}",
            "itemsDelte":"http://localhost/bookschange/shielded-sierra-1174/backend/items/{1}",
            "notifications":"http://localhost/bookschange/shielded-sierra-1174/backend/notifications/",
            "notificationsAdd":"http://localhost/bookschange/shielded-sierra-1174/backend/notifications/",
            "notificationsUpdate":"http://localhost/bookschange/shielded-sierra-1174/backend/notifications/{1}",
            "notificationsDelete":"http://localhost/bookschange/shielded-sierra-1174/backend/notifications/{1}"
          };

          var collections = {

            recommendations : {
              get:function(){
                return recommendations;
              },

              fetch:function(limit){
                limit = limit || limit;
                var url = tokenReplace(urls,recommendations,[limit]);

                $.get(url, item, function(data){
                    recommendations = JSON.parse(data);
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

              fetch: function(limit){
                limit = limit || limit;
                var url = tokenReplace(urls.recommendations,[limit]);

                $.get(url, item, function(data){
                    items = JSON.parse(data);
                },this).error(function(){console.log("Unable to reach backend")});
              },

              search:function(query){
                query = query.toLocaleLowerCase();
                var results = [];
                for (var i in items){
                  var item = items[i];

                  if(item['title'].toLocaleLowerCase().indexOf(query) >= 0){
                    results.push(item);
                    break;
                  }
                    

                  for(var j in item['genre']){
                    var text = item['genre'][j].toLocaleLowerCase();
                    if(text.indexOf(query) >= 0){
                      results.push(item);
                    }
                  }
                }

                return results;


              },

              add: function(item){
                item.fb_id = window.fb_id;
                $.ajax({
                    type: 'POST',
                    contentType: 'application/json',
                    url: urls.itemsAdd,
                    dataType: "json",
                    data: JSON.stringify(item),
                    success: function(data){
                      items.push(item);
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
                $.post(urls.notificationAdd,notification,function(data){
                    notifications.push(notification);
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