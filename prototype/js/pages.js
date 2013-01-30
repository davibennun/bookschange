function Controller (page){
	this.page_id = page;

	this.pages = {
		"1" : function(){
			$("#page8-content").hide();
			$("#page1-content").show();
			$("#page1-content").html(templates.itemsList({data:backend.recommendations.get()}));
			//$("#page1").trigger("create");
		},
		"6":function(){
			console.log("add");


		},
		"7": function(){
			console.log("items");

			$("#page7-content").html(templates.itemsList({data:backend.items.get()}));
			//$('div[data-role="page"]').page();
			//$("#page7").trigger("create");
			
		},
		"5":function(){
			console.log("notify");
			var template = templates.notificationsList({data:backend.notifications.get()});
			$("#page5-content").html(template);
			//$("#page5").trigger("create");
		},
		"2":function(e,data){
			console.log("details");
			var u = $.mobile.path.parseUrl( data.toPage );
			var itemId = u.hash.replace( /.*item_id=/, "" );
			
			// If we cannot get itemId stop working
			if(!itemId && itemId == ""){
			  e.preventDefault();
			  return;
			}

			

			//If result is local or we have to fetch
			if(backend.items.get(itemId).length > 0){
				var result = backend.items.get(itemId)[0];
				$("#item-info-content").html(templates.itemInfo(result));
			}

			if(backend.recommendations.get(itemId).length > 0){
				var result = backend.recommendations.get(itemId)[0];
				$("#item-info-content").html(templates.itemInfo(result));
			}


			//SEARCH VIA BACKEND END POINT
			// backend.items.fetch(itemId,function(result){
			// 		$("#item-info-content").html(templates.itemInfo(result));
			// });


		},
		"8":function(){
			$("#page1-content").hide();
			$("#page8-content").show();
			var query = $("#search-input").val();
			if(query==""){
				var result = {data:backend.items.get()};
			}else{
				var result = {data:backend.items.search(query)};
			}
			
			if(result.data.length==0){
				$("#page8-content").html("<b>Não há itens para mostrar, mas voce pode ser notificado quando se tornar disponível</b>");
				$("#page8-content").append('<a data-role="button" href="#page9" data-cid="button6" class="codiqa-control ui-btn ui-shadow ui-btn-corner-all ui-btn-down-c" style="" data-corners="true" data-shadow="true" data-iconshadow="true" data-iconsize="18" data-wrapperels="span" data-theme="c"><span class="ui-btn-inner ui-btn-corner-all"><span class="ui-btn-text">Criar notificação</span></span></a>');
			}else{
				$("#page8-content").html(templates.itemsList(result));	
			}
		},
		"9":function(){

		}
	}

}

Controller.prototype.go = function(page_id){
	var args = [];
    Array.prototype.push.apply( args, arguments );
    args.shift();
    
	this.pages[page_id].apply(null,args);
}