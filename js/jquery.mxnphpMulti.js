(function($){
	var methods = {
		//Main function, initializes the change and delete listeners for "new" multi-inputs
		init : function(options){
			var defaults = {
				//If the parameters will be read from the html title attribute
				titleParams : false,
				//The id of the Accumulator 
				accumulatorId : "",
				//The Jquery Hidden field object of where the CSV Ids will be inputed
				inputField : "",
				//true child object would have Unique Children false if the children can be repeated
				uniqueChildren : true
			}
			var options =  $.extend(defaults,options);
			return this.each(function(){		
				var settings = getOptions($(this),options);
				//When a dropdown is selected:
				$(this).change(function(e){
					addChildNew($(this).val(),$(this).children("option:selected").html(),settings);
				});				
				//When a delete button for a child is clicked
				$("#"+settings.accumulatorId+" .accumulated.new a").live("click",function(e){
					e.preventDefault();
					var index = $(this).parent().index();
					$(this).parent().remove();
					array = settings.inputField.val();
					array = remove_item(index,array);
					settings.inputField.val(array);
				});
			});
		},
		//Edit Function, initializes the change and delete for "edit" multi-inputs using ajax 
		edit : function(options){
			var defaults = {
				accumulator : "",
				inputField : "",
				actionUrl : "",
				deleteUrl : "",
				parentId : "",
				titleParams : false
			}			
            var options =  $.extend(defaults, options);
			return this.each(function() {
				var settings = getEditOptions($(this),options);				
				//When a dropdown Is selected
				$(this).change(function(e){
					addChildEdit($(this).val(),$(this).children("option:selected").html(),settings,$(this));					
				});				
				//When the delete button for a child is clicked
				$("#"+settings.accumulator.attr("id")+" .accumulated.edit a").live("click",function(e){
					e.preventDefault();
					var pid = $(this).attr("href").split("#");
					var index = $(this).parent().index();
					$(this).parent().remove();
					array = settings.inputField.val();
					array = remove_item(index,array);
					settings.inputField.val(array);
					$.post(settings.deleteUrl,{id:pid[1]});
				});				
			});
		},
		//Add Child to a multi-input
		addChild : function(options){
			var defaults = {
				key : "",
				val : "",
				accumulatorId : "",
				inputField : "",
				titleParams : false,
				editFunct : false,
				uniqueChildren : true
			}
			var options =  $.extend(defaults,options);
			return this.each(function(){
				options = getOptions($(this),options);
				if(!options.editFunct)
					addChildNew(options.key,options.val,options);
				else
					addChildEdit(options.key,options.val,options,$(this));
			});
		},
		//Clear All the children
		clearChildren : function(options){
			var defaults = {
				accumulatorId : "",
				inputField : "",
				titleParams : false
			}
			var options =  $.extend(defaults,options);
			return this.each(function(){
				var settings = getOptions($(this),options);
				$("#"+settings.accumulatorId).html("");
				settings.inputField.val("");
			});
		}
	}
	//Private function to load the options for the "new" multi-select
	function getOptions(select_object,options){
		var settings = {};
		if(options.titleParams){
			var parameters = select_object.attr("title").split(',');
			var settings = {
				accumulatorId : parameters[0],
				inputField : $("#"+parameters[1]),
				uniqueChildren : !(typeof parameters[2] !=  'undefined' && parameters[2] == "not_unique")
			}			
		}
		return $.extend({},options,settings);
	}
	//private function to load the options for the "edit" multi-select
	function getEditOptions(select_object,options){
		var settings = {};
		if(options.titleParams){
			var parameters = select_object.attr("title").split(',');
			var settings = {
				accumulator : $("#"+parameters[0]),
				inputField : $("#"+parameters[1]),
				actionUrl : parameters[2],
				deleteUrl : parameters[3],
				parentId : $("#"+parameters[4]).val(),
				uniqueChildren : !(typeof parameters[5] !=  'undefined' && parameters[5] == "not_unique")
			}
		}
		return $.extend({},options,settings);
	}
	//Private function that adds a child to a 'new' type multi-select
	function addChildNew(key,val,options){	
		if(key != ""){
			if(!options.uniqueChildren || !in_array(options.inputField.val(),key)){
				$("#"+options.accumulatorId).append("<span class='accumulated new'>"+val+"<a href='#'></a></span>");
				if(options.inputField.val()!="") options.inputField.val(options.inputField.val()+",");
				options.inputField.val(options.inputField.val()+key);
			};
		};
	}
	//Private function that adds a child to a 'edit' type multi-select
	function addChildEdit(key,val,options,select_object){
		if(key != ""){						
			if(!options.uniquechildren || !in_array(input_field.val(),key)){
				var spinner = select_object.after("<div class='spinner'></div>").next();
				$.post(options.actionUrl,{parent:options.parentId,son:key},function(data){
					spinner.remove();
					options.accumulator.append("<span class='"+options.accumulator.attr("id")+" accumulated edit'>"+val+"<a href='#"+data+"'></a></span>");
					if(options.inputField.val()!="") options.inputField.val(options.inputField.val()+",");
					options.inputField.val(options.inputField.val()+key);
				});
			};
		};
	}
	//The standard jquery method calling logic
	$.fn.mxnphpMulti = function(method){
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.mxnphpMulti' );
		} 
	}	
})(jQuery);	