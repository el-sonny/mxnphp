(function($){
	//Menu Function
	$.fn.extend({
		mxnphpAdminMenu: function(){
			return this.each(function() {
				var selector = "#"+$(this).attr("id")+" a";
				$(selector).click(function(e){
					e.preventDefault();
					var parent = $(this);
					var child = parent.parent().children("ul");
					if(child.length > 0){
						parent.toggleClass('on');
						child.slideToggle("fast","swing");
						child.toggleClass('on');
					}else{
						window.location = parent.attr("href");
					}
				});
			});
		}
	});
	//SubMenu Function
	$.fn.extend({
		mxnphpAdminSubmenu: function(){
			return this.each(function() {
				$(this).click(function(e){
					e.preventDefault();
					var index = $(this).parent().index();
					var top_container = "#"+$(this).parent().parent().next().attr("id");
					$(this).parent().parent().children("li").children(".on").removeClass("on");
					$(this).parent().parent().children().eq(index).children("a").addClass("on");
					$(top_container+" .container.on").removeClass("on");
					$(top_container+" .container").eq(index).addClass("on");
				});
			});
		}
	});
	//Tabs
	$.fn.extend({
		mxnphpAdminTabs: function(){
			return this.each(function() {
				$(this).click(function(e){
					e.preventDefault();
					var current = $("#content .tabs .on").index();
					var new_tab = $(this).index();
					$("#content .tabs .on").removeClass("on")
					$("#content .tabs a").eq(new_tab).addClass("on");
					$("#content .center").eq(current).hide();
					$("#content .center").eq(new_tab).show();
				});
			});
		}
	});
	//Messages
	$.fn.extend({
		mxnphpAdminMessages: function(){
			return this.each(function() {
				$(this).click(function(e){
					e.preventDefault();
					$("#messages").slideUp("fast");
					$("#messages").hide();
				});
			});
		}
	});
	//Delete Prompt	
	$.fn.extend({
		mxnphpAdminDelete: function(){
			return this.each(function() {
				$(this).click(function(e){
					e.preventDefault();
					url = $(this).attr("href");
					$("#overlay .ans.yes").attr('href',url);
					$("#overlay").fadeIn(80,'swing');
					$("#overlay").css("display","table");
					$("#overlay").css("z-index","4");
				});
				$("#overlay .ans.no").click(function(e){
					e.preventDefault();
					$("#overlay").fadeOut(80,'swing');
				});
				$("#overlay .clickarea").click(function(e){
					$("#overlay").fadeOut(80,'swing',function(){
						if(box != false)
							$("#overlay .box").html(box);
					});
				});
			});
		}
	});
	//Datepicker Function
	$.fn.extend({
		mxnphpDatepicker: function(){
			return this.each(function() {
				var real_input_id = "#"+$(this).next().attr("id");
				$(this).datepicker({
					inline: false,
					altField : real_input_id,
					altFormat : 'yy-mm-dd',
					dateFormat : 'dd/mm/yy'
				});
			});
		}
	});
	//Single Image Function
	$.fn.extend({
		mxnphpSingleImage: function(){
			return this.each(function(){
				var params = $(this).attr("title").split(",");
				if($(this).hasClass("new")){
					$(this).uploadify({
						'uploader'       : '/scripts/uploadify.swf',
						'cancelImg'      : '/scripts/cancel.png',
						'script'         : params[0],
						'auto'           : true,
						'multi'          : false,
						'onComplete'     : mxnphp_new_image,
						'scriptData' 	 : {'swf':'true'}
					});
				}else if($(this).hasClass("edit")){
					$(this).uploadify({
						'uploader'       : '/scripts/uploadify.swf',
						'cancelImg'      : '/scripts/cancel.png',
						'script'         : params[0],
						'auto'           : true,
						'multi'          : false,
						'scriptData' 	 : {id:params[1],'swf':'true'}
					});
				};				
			});
		}
	});
	function mxnphp_new_image(event, queueID, fileObj, response, data){		
		var image = $.parseJSON(response)
		var input = $(event.target).prev();		
		var image_p = input.parent().next();
		var div = mxnphp_make_image(image);
		image_p.html("");
		image_p.append(div);
		input.val(image.filename);
	}
	function mxnphp_make_image(image){
		var img = $(document.createElement('img')).attr("src",image.thumb+"?r="+Math.floor(Math.random()*100001));
		var img_link = $(document.createElement('a')).attr("href",image.full).append(img);
		var delete_link = $(document.createElement('a')).attr("href",image.delete_url).addClass("option_erase").html("erase").click(mxnphp_image_erase);
		var main_container = $(document.createElement('div')).addClass("photo").append(img_link,delete_link);
		return main_container;
	}
	function mxnphp_image_erase(e){
		e.preventDefault();
		var url = $(this).attr("href");
		var container = $(this).parent();
		container.remove();
		$.post(url);
	}
	function in_array(input_string,valor){
		var values = input_string.split(",");
		for(key in values){
			if(values[key] == valor){
				return true;
			}
		}
		return false;
	}
	function remove_item(index,array){
		array = array.split(",");
		var new_array = "";
		for(key in array){
			if(key != index){
				if(new_array != "") new_array = new_array+",";
				new_array = new_array+array[key];
			}
		}
		return new_array;
	}     
})(jQuery);