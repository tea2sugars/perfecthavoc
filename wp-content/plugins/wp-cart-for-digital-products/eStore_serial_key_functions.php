<?php

function eStore_post_sale_retrieve_serial_key_and_update($retrieved_product,$cart_item_name='',$qty=1)
{
	global $wpdb;
        $product_code_data = "";
        $products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
        
        $package_product = eStore_is_package_product($retrieved_product);
        if($package_product)//This is a package product
        {
            eStore_payment_debug('This is a packaged product. Checking if key needs to be issued for the products included in this package.',true);
            $product_ids = explode(',',$retrieved_product->product_download_url);
            if(sizeof($product_ids)>1){
                foreach($product_ids as $id){
                    if(empty($id)){continue;}
                    $retrieved_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
                    $product_code_data .= eStore_get_serial_key_for_product($retrieved_product,$retrieved_product->name,$qty);
                }
            }
            else{
                eStore_payment_debug('Error configuring the bundled product: it needs 2 or more product IDs to create a package product',false);
            }
        }
        else 
        {
            $product_code_data .= eStore_get_serial_key_for_product($retrieved_product,$cart_item_name,$qty);
        }
        return $product_code_data;
}

function eStore_get_serial_key_for_product($retrieved_product,$cart_item_name='',$qty=1)
{
	global $wpdb;
	$product_meta_table_name = WP_ESTORE_PRODUCTS_META_TABLE_NAME;
	$theid = $retrieved_product->id;
	$product_code_data = "";
	$requested_qty = (int)$qty;
	eStore_payment_debug('Checking if a key needs to be issued for product id: '.$theid.' Requested quantity: '.$requested_qty,true);
	$productmeta = $wpdb->get_row("SELECT * FROM $product_meta_table_name WHERE prod_id = '$theid' AND meta_key='available_key_codes'", OBJECT);
	if($productmeta){		
		$available_key_codes = $productmeta->meta_value;
		$key_pieces = explode(WP_ESTORE_SERIAL_KEY_SEPARATOR,$available_key_codes);		
		
		$my_key = "";
		if($requested_qty > 1){
			for($i=0; $i<$requested_qty; $i++){
				if($i>0){$my_key .= ', ';}
				$my_key .= array_pop($key_pieces);
			}
		}else{
			$my_key = array_pop($key_pieces);
		}	

		if(!empty($my_key)){
			if(empty($cart_item_name)){$cart_item_name = $retrieved_product->name;}
			$product_code_data .= "\n".$cart_item_name." - ".$my_key;
			eStore_payment_debug("Serial code that will be issued to this customer: ".$my_key,true);
			
			//Update the DB
			$new_available_key_codes = implode(WP_ESTORE_SERIAL_KEY_SEPARATOR, $key_pieces);
			$updatedb_meta = "UPDATE $product_meta_table_name SET meta_value='$new_available_key_codes' WHERE prod_id='$theid' AND meta_key='available_key_codes'";
			$results = $wpdb->query($updatedb_meta);
			eStore_payment_debug('Updated the serial key values in the database.',true);
		}else{
			eStore_payment_debug('This product does not have any serial key available.',true);
		}
	}
	else{
		eStore_payment_debug('This product does not use the serial key feature',true);
	}	
	return $product_code_data;    
}