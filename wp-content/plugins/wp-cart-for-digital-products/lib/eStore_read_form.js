if(typeof(JS_WP_ESTORE_VARIATION_ADD_STRING) == 'undefined'){
	JS_WP_ESTORE_VARIATION_ADD_STRING = "+";
}
if(typeof(JS_WP_ESTORE_CURRENCY_SYMBOL) == 'undefined'){
	JS_WP_ESTORE_CURRENCY_SYMBOL = "$";
}
if(typeof(JS_WP_ESTORE_VARIATION_THOUSAND_SEPERATOR) == 'undefined'){
	JS_WP_ESTORE_VARIATION_THOUSAND_SEPERATOR = ",";
}
if(typeof(JS_WP_ESTORE_MINIMUM_PRICE_YOU_CAN_ENTER) == 'undefined'){
	JS_WP_ESTORE_MINIMUM_PRICE_YOU_CAN_ENTER = "The minimum amount you can specify is ";
}
variation_add_string = JS_WP_ESTORE_VARIATION_ADD_STRING;//"+";
currency_symbol = JS_WP_ESTORE_CURRENCY_SYMBOL;//"$";
decimal_sep = JS_WP_ESTORE_VARIATION_DECIMAL_SEPERATOR;//".";
thousands_sep = JS_WP_ESTORE_VARIATION_THOUSAND_SEPERATOR;//",";
split_char = "[" + variation_add_string;

function CheckTok (object1)
{	
    var var_add_amt,ary=new Array ();     // where we parse
    ary = val.split ("[");          // break apart
    variation_price_val = ary[1];	
    if(variation_price_val != null)
    {	    	
    	//remove the addition,currency and ] symbol then trim it
    	var_add_amt = variation_price_val.replace(variation_add_string, "");
    	var_add_amt = var_add_amt.replace(currency_symbol, "");
    	var_add_amt = var_add_amt.replace("]", "");
        var_add_amt = var_add_amt.replace(thousands_sep, "");
        var_add_amt = var_add_amt.replace(thousands_sep, "");//one extra for the million separator
        if(decimal_sep!="."){
            var_add_amt = var_add_amt.replace(decimal_sep, ".");
        }
    	var_add_amt = trim(var_add_amt);
    	if(eStoreIsNumeric(var_add_amt)){
    		amt = amt + var_add_amt*1.0;
    	}
    	else{
    		alert("Error! Variation addition amounts are not numeric. Please contact the admin and mention this error.");
    	}
    }
}
function ReadForm1 (object1, buttonType) 
{
    // Read the user form
    var i,j,pos;
    amt=0;val_total="";val_combo="";		
	val_1st_half="";
	variation_name="";
			
    for (i=0; i<object1.length; i++) 
    {     
        // run entire form
        obj = object1.elements[i];           // a form element

        if (obj.type == "select-one") 
        {   // just selects
            if (obj.name == "quantity" ||
                obj.name == "amount") continue;
	        pos = obj.selectedIndex;        // which option selected
	        val = obj.options[pos].value;   // selected value		        
            variation_name = val.split (split_char);          // break apart
            val_combo = val_combo + " (" + trim(variation_name[0]) + ")";
	        CheckTok(object1);
        }
        if (obj.type == "text" || obj.type == "textarea")
        {   // just text
        	if (obj.name == "add_qty") continue;
        	if (obj.name == "custom_price") continue;
	        val = obj.value;//custom text msg value
	        if (val.length != 0)
	        {
	        	val = val.replace("(","[");
	        	val = val.replace(")","]");
	        	val_combo = val_combo + " (" + val + ")";
	        }
        }
    }

	// Now summarize everything we have processed above
	val_total = object1.product_name_tmp1.value + val_combo;
	if(buttonType == 1)
	{
		object1.estore_product_name.value = val_total;
		var price_after_var_change;
		if (object1.custom_price){
			var custom_price = parseFloat(object1.custom_price.value);
			if(!eStore_custom_price_validated(object1,custom_price)){return false;}
			price_after_var_change = custom_price;//custom price overrides the price
		}
		else{
			price_after_var_change = parseFloat(object1.price_tmp1.value) + amt;
		}
		updatePriceAmtText(object1,price_after_var_change,buttonType);
                updateOldPriceAmtText(object1,amt,buttonType);
		object1.price.value = price_after_var_change;
	}
	else if(buttonType == 2)
	{
		object1.item_name.value = val_total;		
		if (object1.custom_price){
			var custom_price = parseFloat(object1.custom_price.value);
			if(!eStore_custom_price_validated(object1,custom_price)){return false;}
			else{
				var price_after_var_change = custom_price + amt;
				updatePriceAmtText(object1,price_after_var_change,buttonType);
                                updateOldPriceAmtText(object1,amt,buttonType);
				object1.amount.value = price_after_var_change;				
			}
		}
		else{
			var price_after_var_change = parseFloat(object1.price_tmp1.value) + amt;
			updatePriceAmtText(object1,price_after_var_change,buttonType);
                        updateOldPriceAmtText(object1,amt,buttonType);
			object1.amount.value = price_after_var_change;
		}
		setCookie("cart_in_use","true",1);
	}
	else if(buttonType == 3)
	{
		object1.item_name.value = val_total;
		if (object1.custom_price){
			var custom_price = parseFloat(object1.custom_price.value);
			if(!eStore_custom_price_validated(object1,custom_price)){return false;}
			else{
				object1.a3.value = custom_price + amt;
			}
		}
		else{			
			object1.a3.value = parseFloat(object1.price_tmp1.value) + amt;
		}
		setCookie("cart_in_use","true",1);
	}		
}
function trim(stringToTrim) {
	return stringToTrim.replace(/^\s+|\s+$/g,"");
}
function setCookie(c_name,value,expiredays)
{
	var exdate=new Date();
	exdate.setDate(exdate.getDate()+expiredays);
	document.cookie=c_name+ "=" +escape(value)+
	((expiredays==null) ? "" : ";expires="+exdate.toUTCString());
}
function eStoreIsNumeric(input)
{
    return (input - 0) == input && input.length > 0;
}
function eStore_custom_price_validated(object1,custom_price)
{
	var min_amt = parseFloat(object1.price_tmp1.value);
	if(isNaN(custom_price) || custom_price < min_amt)
	{
		alert(JS_WP_ESTORE_MINIMUM_PRICE_YOU_CAN_ENTER + min_amt);
		return false;
	}
	return true;
}
function updatePriceAmtText(object1,new_price,buttonType){
	jQuery(document).ready(function($) {//update the price text amount
		var old_price;
		if(buttonType==1){
			old_price = parseFloat(object1.price.value);
		}else if(buttonType==2){
			old_price = parseFloat(object1.amount.value);
		}
		new_price = parseFloat(new_price);
		if(old_price == new_price){//No price value changed
			return;
		}                
                new_price = new_price.toFixed(JS_WP_ESTORE_VARIATION_NUM_OF_DECIMALS);
                if(decimal_sep!="."){//Non english system
                    new_price = new_price.replace(".", decimal_sep);
                }

                var new_prod_price_text = JS_WP_ESTORE_CURRENCY_SYMBOL + new_price;
                if(JS_WP_ESTORE_VARIATION_CURRENCY_POS == 'right'){
                   new_prod_price_text = new_price + JS_WP_ESTORE_CURRENCY_SYMBOL;
                }
		var jq_obj = $(object1);
		var curr_prod_div = jq_obj.closest('.eStore-fancy-wrapper');		
		curr_prod_div.find('.eStore_price_value').text(new_prod_price_text);
	});	
}
function updateOldPriceAmtText(object1,amt,buttonType){
    jQuery(document).ready(function($) {//update the price text amount
        var jq_obj = $(object1);
        var curr_prod_div = jq_obj.closest('.eStore-fancy-wrapper');	
        var cutoff_price_display_val = curr_prod_div.find('.eStore_price_value_old').text();
        if(cutoff_price_display_val) //cut off price is being displayed
        {
            var new_cuttoff_price = parseFloat(object1.price_tmp1_old.value) + amt;
            new_cuttoff_price = new_cuttoff_price.toFixed(JS_WP_ESTORE_VARIATION_NUM_OF_DECIMALS);
            if(decimal_sep!="."){//Non english system
                new_cuttoff_price = new_cuttoff_price.replace(".", decimal_sep);
            }
            var new_cuttoff_price_text = JS_WP_ESTORE_CURRENCY_SYMBOL + new_cuttoff_price; 
            if(JS_WP_ESTORE_VARIATION_CURRENCY_POS == 'right'){
               new_cuttoff_price_text = new_cuttoff_price + JS_WP_ESTORE_CURRENCY_SYMBOL;
            }
                
            curr_prod_div.find('.eStore_price_value_old').text(new_cuttoff_price_text);
        }
    });	
}
