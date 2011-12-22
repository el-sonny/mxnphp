(function($){
	var methods = {
		//Main function switches out selects for custom-select and emulates behaivour
		init : function(options){
			var defaults = {
				
			}
			var options =  $.extend(defaults,options);
			return this.each(function(){		
			
			});			
			
		}
	}
	//The standard jquery method calling logic
	$.fn.{function_name} = function(method){
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call(arguments,1));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.mxnphpMulti' );
		} 
	}	
})(jQuery);	