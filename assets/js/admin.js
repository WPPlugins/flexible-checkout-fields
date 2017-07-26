jQuery.noConflict();
(function($) {
	$(function() {
	
	    var i = 0;
	    
		$('.post-type-shop_order .edit_address .form-field').each(function () {
		    if($(this).width() != 100){
		        if(i % 2 == 0){
		            $(this).addClass('form-left');
		        }
		        else{
		            $(this).addClass('form-right');
		        }
		        i++;
		    }
		    else{
		        i = 0;
		    }
		});
		
	}); 
})(jQuery);
