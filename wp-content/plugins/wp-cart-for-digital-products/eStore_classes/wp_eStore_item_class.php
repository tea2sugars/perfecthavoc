<?php
class WP_eStore_Item
{
	var $item_sub_total;
	var $item_id;
	var $item_name;
	var $item_price;
	var $quantity;
	var $item_tax;
	var $item_shipping;
	var $item_weight;
	var $item_url;
	var $thumb_url = "";
	var $digital_item_flag = false;
	var $item_currency;
	
	function __construct($item_id,$item_name,$item_price,$quantity=1,$item_tax=0,$item_shipping=0,$item_url='')
	{
		$this->set_item_details($item_id,$item_name,$item_price,$quantity,$item_tax,$item_shipping,$item_url);
	}
	function set_item_details($item_id,$item_name,$item_price,$quantity=1,$item_tax=0,$item_shipping=0,$item_url='')
	{
		$this->item_id = $item_id;
		$this->item_name = $item_name;
		$this->item_price = $item_price;
		$this->quantity = $quantity;
		$this->item_tax = $item_tax;
		$this->item_shipping = $item_shipping;
		$this->item_url = $item_url;
		$this->item_sub_total = $this->get_item_sub_total();			
	}	
	function get_item_sub_total()
	{
		return ($this->item_price * $this->quantity);
	}
	function set_digital_item_flag($digital_flag)
	{
		$this->digital_item_flag = $digital_flag;
	}
	function setThumbUrl($thumb_url)
	{
		$this->thumb_url = $thumb_url;
	}	
	function SetItemWeight($item_weight)
	{
		$this->item_weight = $item_weight;
	}	
	function SetItemQty($qty)
	{
		$this->quantity = $qty;
	}		
	function is_digital()
	{
		return $this->digital_item_flag;
	}
	
	function GetItemID()
	{
		return $this->item_id;
	}
	function GetItemName()
	{
		return $this->item_name;
	}
	function GetItemPrice()
	{
		return $this->item_price;
	}
	function GetItemQty()
	{
		return $this->quantity;
	}
	function GetItemTax()
	{
		return $this->item_tax;
	}	
	function GetItemShipping()
	{
		return $this->item_shipping;
	}
	function GetItemWeight()
	{
		return $this->item_weight;
	}	
	function GetItemCurrency()
	{
		return $this->item_currency;
	}
		
	function print_item_details()
	{
		$output = "";
		$output .= "<br />Item ID: ".$this->item_id;
		$output .= "<br />Item Name: ".$this->item_name;
		$output .= "<br />Item Price: ".$this->item_price;
		$output .= "<br />Item Quantity: ".$this->quantity;
		$output .= "<br />Item Tax: ".$this->item_tax;
		$output .= "<br />Item Shipping: ".$this->item_shipping;
		$output .= "<br />Item Weight: ".$this->item_weight;
		$output .= "<br />Item URL: ".$this->item_url;	
		$output .= "<br />Item Sub Total: ".$this->item_sub_total;			
		return $output;
	}
}
?>