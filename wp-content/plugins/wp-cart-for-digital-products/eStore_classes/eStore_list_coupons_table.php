<?php
include_once('eStore-list-table.php');

class eStore_List_Coupons_Table extends WP_eStore_List_Table {

    function __construct(){
        global $status, $page;

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'coupon', //singular name of the listed records
            'plural'    => 'coupons', //plural name of the listed records
            'ajax'      => false //does this table support ajax?
        ) );        
    }

    function column_default($item, $column_name){
    	//Just print the data for that column
    	return $item[$column_name];
    }
    
    function column_id($item){
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="admin.php?page=wp_eStore_discounts&editproduct=%s">Edit</a>',$item['id']),
            'delete'    => sprintf('<a href="?page=%s&delete_record=%s&record_id=%s" onclick="return confirm(\'Are you sure you want to delete this entry?\')">Delete</a>',$_REQUEST['page'],'1',$item['id']),
        );
        
        //Return the id column contents
        return $item['id'] . $this->row_actions($actions);
    }

    /* Custom column output - only use if you have some columns that needs custom output */
    function column_discount_value($item){//Outputs this column output the way we want it
    	$output = "";
		if ($item['discount_type'] == 1){
		    $curr_symbol = WP_ESTORE_CURRENCY_SYMBOL;
		    $output = $curr_symbol.$item['discount_value'];
		}
		else{
			$output = $item['discount_value'].'%';
		}
		if (empty($item['value'])){//not a conditional coupon
		  	$output .= ' Off every product';
		}
		else{
			$output .= ' (if condition met)';
		}
        return $output;
    }
    
    /* Custom column output - only use if you have some columns that needs custom output */
    function column_redemption_limit($item){//Outputs this column output the way we want it
    	$redemption_limit = $item['redemption_limit'];
		if(empty($redemption_limit) || $redemption_limit=='9999'){
			if($redemption_limit != '0'){
				$redemption_limit = '&#8734;';
			}
		}
        return $redemption_limit;
    }
    
    /* Custom column output - only use if you have some columns that needs custom output */
    function column_start_date($item){//Outputs this column output the way we want it
    	$start_date = $item['start_date'];
    	if($start_date == "0000-00-00"){
			$start_date = "-";
    	}
		else{
			//$start_date = date('F j, Y',strtotime($start_date));//Format it nicely
		}
        return $start_date;
    }
    
    /* Custom column output - only use if you have some columns that needs custom output */
    function column_expiry_date($item){//Outputs this column output the way we want it
    	$expiry_date = $item['expiry_date'];
    	if($expiry_date == "0000-00-00"){
			$expiry_date = "No Expiry";
    	}
		else{
			//$expiry_date = date('F j, Y',strtotime($expiry_date));//Format it nicely
		}
        return $expiry_date;
    }
    
    /* overridden function to show a custom message when no records are present */
	function no_items() {
		echo '<p>No Records Found!</p>';
	}

    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'], //Let's reuse singular label
            /*$2%s*/ $item['id'] //The value of the checkbox should be the record's key/id
        );
    }
    
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'id'     => 'Coupon ID',
            'coupon_code'    => 'Coupon Code',
            'discount_value'  => 'Discount Value',
            'redemption_limit'  => 'Redemption Limit',
            'redemption_count'  => 'Redemption Count',
            'start_date'  => 'Start (yyyy-mm-dd)',
            'expiry_date'  => 'Expiry (yyyy-mm-dd)',
			'active'  => 'Active',
        );
        return $columns;
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'id'     => array('id',true),     //true means its already sorted
            'coupon_code'    => array('coupon_code',false),
            'start_date'  => array('start_date',false),
        	'expiry_date'  => array('expiry_date',false)
        );
        return $sortable_columns;
    }
    
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }

    function process_bulk_action() {        
        //Detect when a bulk action is being triggered... //print_r($_GET);
        if( 'delete'===$this->current_action() ) {
        	$nvp_key = $this->_args['singular'];
        	$records_to_delete = $_GET[$nvp_key];
        	if(empty($records_to_delete)){
        		echo '<div id="message" class="updated fade"><p>Error! You need to select multiple records to perform a bulk action!</p></div>';
        		return;
        	}
        	foreach ($records_to_delete as $row){
			    global $wpdb;
				$record_table_name = WP_ESTORE_COUPON_TABLE_NAME;//The table name for the records			
				$updatedb = "DELETE FROM $record_table_name WHERE id='$row'";
				$results = $wpdb->query($updatedb);
        	}
        	echo '<div id="message" class="updated fade"><p>Selected records deleted successfully!</p></div>';
        }        
    }
    
    function prepare_items() {        
        // Lets decide how many records per page to show     
        $per_page = 25;
                
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
                
        $this->_column_headers = array($columns, $hidden, $sortable);        
        
        $this->process_bulk_action();                              
        
        // This checks for sorting input and sorts the data.
        $orderby_column = isset($_GET['orderby'])?$_GET['orderby']:'';
        $sort_order = isset($_GET['order'])?$_GET['order']:'';
        if(empty($orderby_column)){
        	$orderby_column = "id";
        	$sort_order = "DESC";
        }
		global $wpdb;
		$records_table_name = WP_ESTORE_COUPON_TABLE_NAME;//The table to query
		$resultset = $wpdb->get_results("SELECT * FROM $records_table_name WHERE dynamic='' ORDER BY $orderby_column $sort_order", OBJECT);
		$data = array();
		$data = json_decode (json_encode ($resultset), true);

		//pagination requirement
        $current_page = $this->get_pagenum();
        
        //pagination requirement
        $total_items = count($data);        
        
        //pagination requirement
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);                
        
        // Now we add our *sorted* data to the items property, where it can be used by the rest of the class.
        $this->items = $data;        
        
        //pagination requirement
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }    
}
