jQuery(document).ready(function($){
$(function(){
	//$('.eStore_add_to_cart_button').click(function(){
	$('.eStore-button-form').on('submit', function() {
		var $thisbuttonform = $(this);
		var proddatavalues = $thisbuttonform.serialize();		
		nonce = eStore_JS.add_cart_nonce;
		postUrl = eStore_JS.ajaxurl;
                var cart_shortcodes_in_use = new Array();
                var cart_shortcodes_ref = estore_get_cart_shortcodes_reference_array();
                for (var i=0; i<cart_shortcodes_ref.length; i++)
                { 
                    if($('div').hasClass('estore-cart-wrapper-'+i)){
                        cart_shortcodes_in_use[i] = '1';
                    }
                    else{
                        cart_shortcodes_in_use[i] = '';
                    }
                }
                cart_shortcodes_in_use = JSON.stringify(cart_shortcodes_in_use);
                /* use it on the other hand to decode
                decoded_cart_shortcode = JSON.parse(cart_shortcodes_in_use);
                */
		//$thisbuttonform.addClass('eStore_ajax_loading').block({message: null, overlayCSS: {background:'url(' + eStore_JS.estore_url + '/images/ajax-loader3.gif) no-repeat center', opacity: '0.8'}});
		//The block works good when there is a wrapper class wrapping around the form and that class is blocked
		//$thisbuttonform.addClass('eStore_ajax_loading').block({message: "Adding", css: {background:'url(' + eStore_JS.estore_url + '/images/ajax-loader3.gif) no-repeat center', opacity: '0.8'}});
		//$thisbuttonform.addClass('eStore_item_added');
		$thisbuttonform.addClass('eStore_ajax_loading');
		
		$.ajax({
			type : "post",
			dataType : "json",
			url : postUrl,
			data : {action:"estore_add_cart_submit", eStore_cart_action:'add_to_cart', prod_data:proddatavalues, nonce:nonce, cart_shortcodes:cart_shortcodes_in_use},
			success: function(response){
	        	if(response.status == "success") {
	        		//alert("Success from the server!");                        
	            	//$(".eStore_classic_cart_wrapper").html(response.output);
                        //
                        estore_print_shopping_cart_in_use(response.cart_shortcodes);
                        //
	            	estore_update_cart_validation();
                        estore_item_dynamically_added_to_cart_msg(response.prod_id);
	        	}else if(response.status == "error") {
	        		var msg = response.output;
	        		$.blockUI({
	        			message: msg, 
	        			css: {width:'350px',border:'none',padding:'15px',backgroundColor:'#000',opacity:0.8},
	        			showOverlay: false,
	        			fadeIn: 700,
	        			fadeOut: 700,
	        			timeout: 2000   			
	        		});
	        	}else {
	            	alert("Item could not be added to the cart. Please try again.");
	            }
	        	$thisbuttonform.removeClass('eStore_ajax_loading');
	        	//$thisbuttonform.removeClass('eStore_ajax_loading').unblock();//unblock
			}
		});//end $.ajax
		return false;//don't submit the form
	});//end eStore_add_to_cart_button.click	

        function estore_item_dynamically_added_to_cart_msg(prod_id)
        {
            var added_img = eStore_JS.estore_url + '/images/check.png';
            $(".eStore_item_added_msg-"+prod_id).html('<img src="'+added_img+'">');
        }
    
	function estore_update_cart_validation()
        {
            if ($('.t-and-c').length ) {
            //Terms and condtions is being used so apply t and c validation
                $('.t_and_c_error').hide();
                $(".t-and-c").click(function(){
                        if($(".t-and-c").is(':checked')){  
                                $.cookie("eStore_submit_payment","true",{path: '/'}); 
                                $('.t_and_c_error').hide();          
                        }
                        else{
                                $.cookie("eStore_submit_payment","false",{path: '/'});	
                        }                 
                }); 
                $(".eStore_paypal_checkout_button").click(function(e){
                        if(!$(".t-and-c").is(':checked')){
                                $('.t_and_c_error').show();
                                e.preventDefault();         
                        }
                });
            }
            //Shipping var changed			
            var shipping_var_warning_class = $('.shipping_var_changed');
            var shipping_var_warning_default_class = $('.shipping_var_changed_default');
            shipping_var_warning_class.hide();
            $('.shipping_variation').change(function(){
                shipping_var_warning_default_class.hide();
                shipping_var_warning_class.show();
            });
            var eStore_shipping_var_needs_update = false;
            if(shipping_var_warning_class.is(":visible")){eStore_shipping_var_needs_update = true;}
            else if(shipping_var_warning_default_class.is(":visible")){eStore_shipping_var_needs_update = true;}	
            $(".eStore_paypal_checkout_button").click(function(e){//Check if shipping variation has been selected
                if(eStore_shipping_var_needs_update){
                shipping_var_warning_class.css({'border':'1px solid red','padding':'5px'});
                shipping_var_warning_default_class.css({'border':'1px solid red','padding':'5px'});
                e.preventDefault();
                }
            });
            //Qty change
            var eStore_cart_item_qty = $('.eStore_cart_item_qty');
            eStore_cart_item_qty.keypress(function(){
                $('.eStore_qty_change_pinfo').show();
            });
            //checkout button
            $(".eStore_gateway").change(function(){
            var selected = $(this);
            var output = "";
            if(selected.val() == "paypal"){
                $.cookie("eStore_gateway", "paypal",{path: '/'});
                image = eStore_JS.estore_url + '/images/checkout_paypal.png';
                $('.eStore_paypal_checkout_button').attr("src", image);
            }
            if(selected.val() == "manual"){
                $.cookie("eStore_gateway", "manual",{path: '/'});
                image = eStore_JS.estore_url + '/images/checkout_manual.png';
                $('.eStore_paypal_checkout_button').attr("src", image);
            }
            if(selected.val() == "2co"){
                $.cookie("eStore_gateway", "2co",{path: '/'});
                image = eStore_JS.estore_url + '/images/checkout_2co.png';
                $(".eStore_paypal_checkout_button").attr("src", image);
            }
            if(selected.val() == "authorize"){
                $.cookie("eStore_gateway", "authorize",{path: '/'});
                image = eStore_JS.estore_url + '/images/checkout_authorize.gif';
                $(".eStore_paypal_checkout_button").attr("src", image);
            }
            //payment gateway bundle ones will need to be fixed
            $(".eStore_gateway").each(function(){
                    $(this).val(selected.val());
                });
            });		
	}
        
        function estore_get_cart_shortcodes_reference_array()
        {
            //Definition of all our existing various different cart shortcodes
            var cart_shortcodes_ref = new Array();
            cart_shortcodes_ref[0] = "";  //classic cart
            cart_shortcodes_ref[1] = "";  //fancy cart1
            cart_shortcodes_ref[2] = "";  //fancy cart2
            cart_shortcodes_ref[3] = "";  //classic cart with thumb
            cart_shortcodes_ref[4] = "";  //compact cart 1
            cart_shortcodes_ref[5] = "";  //compact cart 2
            cart_shortcodes_ref[6] = "";  //compact cart 3
            cart_shortcodes_ref[7] = "";  //compact cart 4            
            return cart_shortcodes_ref; 
        }
        
        function estore_print_shopping_cart_in_use(cart_shortcodes_output)
        {
            for (var i=0; i<cart_shortcodes_output.length; i++)
            { 
                if(cart_shortcodes_output[i] != ""){
                    $(".estore-cart-wrapper-"+i).replaceWith(cart_shortcodes_output[i]);
                }
            }
        }
});
});