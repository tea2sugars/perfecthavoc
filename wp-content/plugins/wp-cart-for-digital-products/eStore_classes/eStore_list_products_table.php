<?php
include_once('eStore-list-table.php');

class eStore_List_Products_Table extends WP_eStore_List_Table {

    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'product', //singular name of the listed records
            'plural'    => 'products', //plural name of the listed records
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
            'edit'      => sprintf('<a href="admin.php?page=wp_eStore_addedit&editproduct=%s">Edit</a>',$item['id']),
            'delete'    => sprintf('<a href="?page=%s&Delete=%s&prod_id=%s" onclick="return confirm(\'Are you sure you want to delete this entry?\')">Delete</a>',$_REQUEST['page'],'1',$item['id']),
        );
        
        //Return the id column contents
        return $item['id'] . $this->row_actions($actions);
    }

    /* Custom column output - only use if you have some columns that needs custom output */
    function column_thumbnail_url($item){//Outputs the thubmnail image the way we want it
    	$output = "";
    	$thumb_src = $item['thumbnail_url'];
		if(!empty($thumb_src)){
			$output .= '<img src="'.$thumb_src.'" width="50" height="50">';
		}
		else{
			$output .= '<img src="'.WP_ESTORE_URL.'/images/no-image-specified.gif" width="50" height="50">';
		}
        //Return the output for the "thumbnail image" column content
        return $output;
    }
    
    /* Custom column output - only use if you have some columns that needs custom output */
    function column_available_copies($item){//Outputs the thubmnail image the way we want it
    	$column_value = $item['available_copies'];
		if(empty($column_value)){
			if($column_value == ''){
				$column_value = '&#8734;';					
			}
			else{
				$column_value = '0';
			}
		}
        return $column_value;
    }
    
    function column_product_actions($item){//Outputs the action items
    	$column_value = sprintf('<a href="admin.php?page=wp_eStore_addedit&editproduct=%s">Edit</a>',$item['id']);
    	$column_value .= " | ";
    	$column_value .= sprintf('<a href="?page=%s&Delete=%s&prod_id=%s" onclick="return confirm(\'Are you sure you want to delete this entry?\')">Delete</a>',$_REQUEST['page'],'1',$item['id']);
        $column_value .= " | ";
        //$column_value .= sprintf('<a href="?page=%s&estore_copy_product=%s&src_prod_id=%s" onclick="return confirm(\'Are you sure you want to make a copy of this product?\')">Copy</a>',$_REQUEST['page'],'1',$item['id']);
        $column_value .= sprintf('<a href="admin.php?page=wp_eStore_addedit&amp;copy_product=%s&orig_product_id=%s" onclick="return confirm(\'Are you sure you want to make a copy of this product?\')">Copy</a>','1',$item['id']);
        return $column_value;
    }
    
    /* overridden function to show a custom message when no records are present */
	function no_items() {
		echo '<br />No Products Found!  <a href="admin.php?page=wp_eStore_addedit" class="button rbutton">Add A New Product</a><br /><br />';
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
            'id'     => 'ID',
            'thumbnail_url'    => 'Image',
            'name'  => 'Product Name',
            'price'  => 'Price',
            'product_download_url'  => 'Download Link',
            'sales_count'  => 'Sales Count',
            'available_copies'  => 'Available Copies',
        	'product_actions'  => 'Actions'
        );
        return $columns;
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'id'     => array('id',false),     //true means its already sorted
            'name'    => array('name',false),
            'price'  => array('price',false)
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
				$record_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;//The table name for the records			
				$updatedb = "DELETE FROM $record_table_name WHERE id='$row'";
				$results = $wpdb->query($updatedb);
        	}
        	echo '<div id="message" class="updated fade"><p>Selected records deleted successfully!</p></div>';
        }        
    }    
    
    function prepare_items() {        
        // Lets decide how many records per page to show     
        $per_page = get_option('eStore_manage_products_limit2');//50
                
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
		$records_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;//The table to query
		$resultset = $wpdb->get_results("SELECT * FROM $records_table_name ORDER BY $orderby_column $sort_order", OBJECT);
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
