jQuery(document).ready(function($) {
$(function() {
   $(".eStore_gateway").change(function(){
            var selected = $(this);
            if(selected.val() == "paypal"){
            	$.cookie("eStore_gateway", "paypal",{path: '/'});
                image = JS_WP_ESTORE_URL + '/images/checkout_paypal.png';
                $('.eStore_paypal_checkout_button').attr("src", image);
            }
            if(selected.val() == "manual"){
            	$.cookie("eStore_gateway", "manual",{path: '/'});
                image = JS_WP_ESTORE_URL + '/images/checkout_manual.png';
                $('.eStore_paypal_checkout_button').attr("src", image);
            }
            if(selected.val() == "2co"){
            	$.cookie("eStore_gateway", "2co",{path: '/'});
                image = JS_WP_ESTORE_URL + '/images/checkout_2co.png';
                $(".eStore_paypal_checkout_button").attr("src", image);
            }
            if(selected.val() == "authorize"){
            	$.cookie("eStore_gateway", "authorize",{path: '/'});
                image = JS_WP_ESTORE_URL + '/images/checkout_authorize.gif';
                $(".eStore_paypal_checkout_button").attr("src", image);
            }
            if(selected.val() == "gco"){
            	$.cookie("eStore_gateway", "gco",{path: '/'});
                image = JS_WP_ESTORE_PG_BUNDLE_URL + '/images/checkout_gco.gif';
                $(".eStore_paypal_checkout_button").attr("src", image);
            } 
            if(selected.val() == "pppro"){
            	$.cookie("eStore_gateway", "pppro",{path: '/'});
                image = JS_WP_ESTORE_PG_BUNDLE_URL + '/images/checkout_pppro.gif';
                $(".eStore_paypal_checkout_button").attr("src", image);
            }    
            if(selected.val() == "sagepay"){
            	$.cookie("eStore_gateway", "sagepay",{path: '/'});
                image = JS_WP_ESTORE_PG_BUNDLE_URL + '/images/checkout_sagepay.gif';
                $(".eStore_paypal_checkout_button").attr("src", image);
            }   
            if(selected.val() == "auth_aim"){
            	$.cookie("eStore_gateway", "auth_aim",{path: '/'});
                image = JS_WP_ESTORE_PG_BUNDLE_URL + '/images/checkout_auth_aim.gif';
                $(".eStore_paypal_checkout_button").attr("src", image);
            }
            if(selected.val() == "eway"){
            	$.cookie("eStore_gateway", "eway",{path: '/'});
                image = JS_WP_ESTORE_PG_BUNDLE_URL + '/images/checkout_eway.gif';
                $(".eStore_paypal_checkout_button").attr("src", image);
            } 
            if(selected.val() == "epay_dk"){
            	$.cookie("eStore_gateway", "epay_dk",{path: '/'});
                image = JS_WP_ESTORE_PG_BUNDLE_URL + '/images/checkout_epay_dk.gif';
                $(".eStore_paypal_checkout_button").attr("src", image);
            }
            if(selected.val() == "verotel"){
            	$.cookie("eStore_gateway", "verotel",{path: '/'});
                image = JS_WP_ESTORE_PG_BUNDLE_URL + '/images/checkout_verotel.gif';
                $(".eStore_paypal_checkout_button").attr("src", image);
            }
            if(selected.val() == "freshbooks"){
            	$.cookie("eStore_gateway", "freshbooks",{path: '/'});
                image = JS_WP_ESTORE_PG_BUNDLE_URL + '/images/checkout_freshbooks.gif';
                $(".eStore_paypal_checkout_button").attr("src", image);
            }
       $(".eStore_gateway").each(function(){
            $(this).val(selected.val());
       });
   });
   
   set_co_img_to_selected_gateway();
   function set_co_img_to_selected_gateway(){     
        var selected = $('.eStore_gateway');
        if(selected.val() == "paypal"){
            $.cookie("eStore_gateway", "paypal",{path: '/'});
            image = JS_WP_ESTORE_URL + '/images/checkout_paypal.png';
            $('.eStore_paypal_checkout_button').attr("src", image);
        }
        if(selected.val() == "manual"){
            $.cookie("eStore_gateway", "manual",{path: '/'});
            image = JS_WP_ESTORE_URL + '/images/checkout_manual.png';
            $('.eStore_paypal_checkout_button').attr("src", image);
        }
        if(selected.val() == "2co"){
            $.cookie("eStore_gateway", "2co",{path: '/'});
            image = JS_WP_ESTORE_URL + '/images/checkout_2co.png';
            $(".eStore_paypal_checkout_button").attr("src", image);
        }
        if(selected.val() == "authorize"){
            $.cookie("eStore_gateway", "authorize",{path: '/'});
            image = JS_WP_ESTORE_URL + '/images/checkout_authorize.gif';
            $(".eStore_paypal_checkout_button").attr("src", image);
        }
   }
 });
 });