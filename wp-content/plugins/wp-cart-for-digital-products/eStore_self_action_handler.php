<?php

/* Hanldes do_action hooks that eStore uses itself */
add_action('eStore_action_cart_data_updated', 'eStore_action_cart_data_updated_handler');
add_action('eStore_action_cart_coupon_applied', 'eStore_action_cart_coupon_applied_handler');
add_action('eStore_action_after_cart_items_details', 'eStore_action_after_cart_items_details_handler');
add_filter('eStore_change_curr_code_before_payment_filter', 'estore_buy_now_currency_override_handler');

function eStore_action_cart_data_updated_handler()
{
    estore_check_and_reapply_area_specific_tax();
    //Add other tasks below
    
}

function eStore_action_cart_coupon_applied_handler()
{
    estore_check_and_reapply_area_specific_tax();
    //Add other after coupon applied tasks below
    
}

function eStore_action_after_cart_items_details_handler()
{
    if (isset($_POST['eStore_store_pickup_data_submitted'])){
        if (WP_ESTORE_DO_NOT_APPLY_SHIPPING_FOR_STORE_PICKUP != '0')
        {            
            if(!empty($_POST['eStore_store_pickup_chkbx_data'])){ 
                //Store pickup toggled to unhcecked/off
                $_SESSION['eStore_cart_postage_cost'] = eStore_get_cart_shipping();
                unset($_SESSION['eStore_store_pickup_checked']);
                unset($_SESSION['eStore_last_action_msg']);                
            }
            else{
                //remove shipping from the shopping cart as the user has selected to do store pickup
                $_SESSION['eStore_cart_postage_cost'] = 0;
                $_SESSION['eStore_store_pickup_checked'] = '1';
                $_SESSION['eStore_last_action_msg'] = '<p style="color: green;">'.WP_ESTORE_STORE_PICKUP_SELECTED.'</p>'; 
            }
        }
    }  
}

/* The following functions are useful for the above action handlers */
function estore_check_and_reapply_area_specific_tax()
{
    if (WP_ESTORE_APPLY_TAX_FOR_CERTAIN_AREA !== '0')//Apply sales tax for this customer
    {
        if(isset($_SESSION['eStore_area_specific_total_tax'])){
            $_SESSION['eStore_area_specific_total_tax'] = eStore_calculate_total_cart_tax();
        }
    }    
}

/* this function overrides the currency value if Buy Now currency code is specified in that product */
function estore_buy_now_currency_override_handler($currency_code)
{
    if(isset($_SESSION['eStore_buy_now_currency_override']) && !empty($_SESSION['eStore_buy_now_currency_override']))
    {
        $currency_code = $_SESSION['eStore_buy_now_currency_override'];
        unset($_SESSION['eStore_buy_now_currency_override']);
    }
    return $currency_code; 
}