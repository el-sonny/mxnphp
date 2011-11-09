(function($){
	//Multi Edit For New
    $.fn.extend({
        mxnphpMultiSelect: function() {
            return this.each(function() {
				var parameters = $(this).attr("title").split(',');
				var accumulator_id = parameters[0];
				var input_field = $("#"+parameters[1]);
				//funcion multi-select
				$(this).change(function(e){
					var key = $(this).val();
					val = $(this).children("option:selected").html();	
					if(key != ""){
						if(!in_array(input_field.val(),key)){
							$("#"+accumulator_id).append("<span class='accumulated new'>"+val+"<a href='#'></a></span>");
							if(input_field.val()!="") input_field.val(input_field.val()+",");
							input_field.val(input_field.val()+key);
						};
					};
				});
				
				//funcion delete-select
				$("#"+accumulator_id+" .accumulated.new a").live("click",function(e){
					e.preventDefault();
					var index = $(this).parent().index();
					$(this).parent().remove();
					array = input_field.val();
					array = remove_item(index,array);
					input_field.val(array);
				});
				
			});
        }	
    });
	//Multi-input for Edit
	$.fn.extend({
        mxnphpMultiEdit: function() {
            /*var defaults = {
				acumulator : "", // El acumulador gráfico de los items
				input_field : "", // El input acumulador de los id's
				action_url : "", //función para agregar usuario
				parent_id : "", //input acumulador de archivo actual
				url : "", //función para borrar usuario
            }*/
            //var options =  $.extend(defaults, options);
            return this.each(function() {
				var parameters = $(this).attr("title").split(',');
				var accumulator = $("#"+parameters[0]);
				var input_field = $("#"+parameters[1]);
				var action_url = parameters[2];
				var delete_url = parameters[3];
				var parent_id = $("#"+parameters[4]).val();
				
				//funcion multi-select edit
				$(this).change(function(e){
					var key = $(this).val();
					var val = $(this).children("option:selected").html();
					if(key != ""){						
						if(!in_array(input_field.val(),key)){
							var spinner = $(this).after("<div class='spinner'></div>").next();
							$.post(action_url,{parent:parent_id,son:key},function(data){
								spinner.remove();
								accumulator.append("<span class='"+accumulator.attr("id")+" accumulated edit'>"+val+"<a href='#"+data+"'></a></span>");
								if(input_field.val()!="") input_field.val(input_field.val()+",");
								input_field.val(input_field.val()+key);
							});
						};
					};
				});
				
				//funcion delete-select edit
				$("#"+accumulator.attr("id")+" .accumulated.edit a").live("click",function(e){
					e.preventDefault();
					var pid = $(this).attr("href").split("#");
					var index = $(this).parent().index();
					$(this).parent().remove();
					array = input_field.val();
					array = remove_item(index,array);
					input_field.val(array);
					$.post(delete_url,{id:pid[1]},function(data){
					// 			alert(data);
					});

				});
				
			});
        }
    });
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