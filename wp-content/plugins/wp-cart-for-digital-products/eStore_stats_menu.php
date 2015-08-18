<?php
include_once('admin_includes.php');

$products_table_name = $wpdb->prefix . "wp_eStore_tbl";
$customer_table_name = $wpdb->prefix . "wp_eStore_customer_tbl";
$coupon_table_name = $wpdb->prefix . "wp_eStore_coupon_tbl";
$sales_table_name = $wpdb->prefix . "wp_eStore_sales_tbl";

function wp_estore_stats_menu()
{
    echo '<div class="wrap">
    <h2>'.__('WP eStore Stats', 'wp_eStore').'</h2>';

    echo '<div id="poststuff"><div id="post-body">';
    echo '<script type="text/javascript" src="https://www.google.com/jsapi"></script>';
    
    global $wpdb,$wp_eStore_config;
    global $products_table_name;
    	
    $curr_date = (date ("Y-m-d"));
    $m = date('m');
    $y = date('Y');
    $start_date = $y.'-'.$m.'-01';
    $end_date = $curr_date;
    $currency = get_option('cart_payment_currency');
	
    if(isset($_POST['show_stats_between_dates']))
    {
    	//Show stats between dataes
    	$start_date = $_POST['stat_start_date'];
    	$end_date = $_POST['stat_end_date'];
    }
    
    //preload some stats data
    eStore_load_customers_data_into_array_between_dates($start_date,$end_date);
    $item_stats = eStore_itemised_sale_stat_between_dates_new($start_date,$end_date);
    
    //TODO - stats to add - top ten buyer, top coupons used
    
    //$eStore_customers_data = $wp_eStore_config->getValue('eStore_customers_data_between_dates');
    //$eStore_customers_data_unique_txns = $wp_eStore_config->getValue('eStore_customers_data_between_dates_unique_txns');
    //$eStore_purchase_by_date_data = $wp_eStore_config->getValue('eStore_purchase_by_date_data_between_dates');    
    
    echo '<div class="postbox">
    <h3><label for="title">Choose Date Range (yyyy-mm-dd)</label></h3>
    <div class="inside">';

    ?>	
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    Start Date: <input class="estore_date" name="stat_start_date" type="text" id="stat_start_date" value="<?php echo $start_date; ?>" size="12" />
    End Date: <input class="estore_date" name="stat_end_date" type="text" id="stat_end_date" value="<?php echo $end_date; ?>" size="12" />
    <div class="submit">
    <input type="submit" name="show_stats_between_dates" class="button-primary" value="<?php _e('View Stats'); ?> &raquo;" />
    </div>	
    </form>	
    <?php 
    echo '</div></div>';
	
	echo '
	<div class="postbox">
	<h3><label for="title">Overview</label></h3>
	<div class="inside">';
	
	echo '
	<table width="350">';

            echo '<tr>';
            echo '<td>Total Number of Products : </td>';
            echo '<td><strong>'.eStore_total_num_products().'</strong></td>';
            echo '</tr>';

            echo '<tr>';
            echo '<td>Total Number of Coupons : </td>';
            echo '<td><strong>'.eStore_total_num_coupons().'</strong></td>';
            echo '</tr>';

            echo '<tr>';
            echo '<td>Number of Transactions : </td>';
            echo '<td><strong>'.eStore_num_transactions_between_dates($start_date,$end_date).'</strong></td>';
            echo '</tr>';

            echo '<tr>';
            echo '<td>Number of Different Items Sold : </td>';
            echo '<td><strong>'.eStore_num_diff_items_sold_between_dates($start_date,$end_date).'</strong></td>';
            echo '</tr>';

            echo '<tr>';
            echo '<td>Total Item Quantity Sold : </td>';
            echo '<td><strong>'.estore_total_qty_sold_between_dates($start_date,$end_date).'</strong></td>';
            echo '</tr>';                

            echo '<tr>';
            echo '<td>Total Sales Amount : </td>';
            echo '<td><strong>'.eStore_sale_amount_between_dates($start_date,$end_date).'</strong></td>';
            echo '<td>'.$currency.'</td>';
            echo '</tr>';

            echo '<tr>';
            echo '<td>Refund Issued : </td>';
            echo '<td><strong>'.eStore_num_refunds_between_dates($start_date,$end_date).'</strong></td>';
            echo '<td>Transactions</td>';
            echo '</tr>';

            echo '<tr>';
            echo '<td>Total Refund Amount : </td>';
            echo '<td><strong>'.eStore_refund_amount_between_dates($start_date,$end_date).'</strong></td>';
            echo '<td>'.$currency.'</td>';
            echo '</tr>';

            echo '<tr>';
            echo '<td>Net Sales Amount : </td>';
            echo '<td><strong>'.eStore_net_amount_between_dates($start_date,$end_date).'</strong></td>';
            echo '<td>'.$currency.'</td>';
            echo '</tr>';
		
	echo '</table>';
	echo '</div></div>';

	echo '<h3 style="font-size:18px;font-weight:bold;margin-bottom:10px;border-bottom:1px solid #ccc;">Sale Stats by Date</h3>';
	eStore_draw_sale_stat_by_date();
		
	echo '<h3 style="font-size:18px;font-weight:bold;margin-bottom:10px;border-bottom:1px solid #ccc;">Itemized Sale Stats</h3>';
	eStore_draw_itemisied_sale_stat($item_stats);
	echo '<br />';
        echo '<table class="widefat">
                <thead><tr>
                <th scope="col">'.__('Item ID', 'wp_eStore').'</th>
                <th scope="col">'.__('Item Name', 'wp_eStore').'</th>
                <th scope="col">'.__('Average Sale Price ('.$currency.')', 'wp_eStore').'</th>
                <th scope="col">'.__('Quantity Sold', 'wp_eStore').'</th>
                <th scope="col">'.__('Sale Total ('.$currency.')', 'wp_eStore').'</th>
                </tr></thead>
                <tbody>';

        foreach ($item_stats as $item)
        {
            $id = $item['item_id'];
            $ret_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$id'", OBJECT);
            $avg_sale_price = number_format($item['sale_total']/$item['qty_sold'],2);
            $item_sale_total = number_format(floatval($item['sale_total']),2);
            echo '<tr>';
            echo "<td>". $item['item_id'] ."</td><td>". $ret_product->name ."</td><td>".$avg_sale_price ."</td><td>". $item['qty_sold'] ."</td><td>". $item_sale_total."</td>";
            echo '</tr>';
        }

        echo '</tbody></table>';

    echo '</div></div>';
    echo '</div>';
}

function eStore_total_num_products()
{
    global $wpdb;
    global $products_table_name;
    $query = $wpdb->get_row("SELECT count(*) as total_record FROM $products_table_name", OBJECT);
    $number_of_products = $query->total_record;
    if (empty($number_of_products))
    {
        $number_of_products = "0";
    }
    return $number_of_products;
}
function eStore_total_num_coupons()
{
    global $wpdb;
    global $coupon_table_name;
    $query = $wpdb->get_row("SELECT count(*) as total_record FROM $coupon_table_name", OBJECT);
    $number_of_coupons = $query->total_record;
    if (empty($number_of_coupons))
    {
        $number_of_coupons = "0";
    }
    return $number_of_coupons;
}
function estore_total_qty_sold_between_dates($start_date,$end_date)
{
    $total_qty_sold = 0;
    $wp_eStore_config = WP_eStore_Config::getInstance();
    $itemized_stats = $wp_eStore_config->getValue('eStore_itemized_sale_stat_between_dates');
    foreach($itemized_stats as $item){
        $total_qty_sold = $total_qty_sold + $item['qty_sold'];            
    }
    return $total_qty_sold;
}
function eStore_num_diff_items_sold_between_dates($start_date,$end_date)
{
    $wp_eStore_config = WP_eStore_Config::getInstance();
    $itemized_stats = $wp_eStore_config->getValue('eStore_itemized_sale_stat_between_dates');
    $item_count = count($itemized_stats);
    return $item_count;
}
function eStore_num_transactions_between_dates($start_date,$end_date)
{
    global $wp_eStore_config;
    $unqie_txn_customer_data = $wp_eStore_config->getValue('eStore_customers_data_between_dates_unique_txns');
    $number_of_txn = count($unqie_txn_customer_data);
    if (empty($number_of_txn))
    {
        $number_of_txn = "0";
    }
    return $number_of_txn;
}
function eStore_sale_amount_between_dates($start_date,$end_date)
{
    global $wpdb;
    global $sales_table_name;
    $row = $wpdb->get_row("select SUM(sale_price) AS total from $sales_table_name where sale_price > 0 AND date BETWEEN '$start_date' AND '$end_date'", OBJECT);
    $total_sales = round($row->total,2);
    if (empty($total_sales))
    {
        $total_sales = "0.00";
    }
    return $total_sales;
}

function eStore_num_refunds_between_dates($start_date,$end_date)
{
    global $wpdb;
    global $sales_table_name;
    $query = $wpdb->get_row("SELECT count(*) as total_record FROM $sales_table_name WHERE sale_price < 0 AND date BETWEEN '$start_date' AND '$end_date'", OBJECT);
    $number_of_sales = $query->total_record;
    if (empty($number_of_sales))
    {
        $number_of_sales = "0";
    }
    return $number_of_sales;
}
function eStore_refund_amount_between_dates($start_date,$end_date)
{
    global $wpdb;
    global $sales_table_name;
    $row = $wpdb->get_row("select SUM(sale_price) AS total from $sales_table_name where sale_price < 0 AND date BETWEEN '$start_date' AND '$end_date'", OBJECT);
    $total_sales = round($row->total,2);
    if (empty($total_sales))
    {
        $total_sales = "0.00";
    }
    return $total_sales;
}
function eStore_net_amount_between_dates($start_date,$end_date)
{
    return (eStore_sale_amount_between_dates($start_date,$end_date) + eStore_refund_amount_between_dates($start_date,$end_date));
}

function eStore_draw_sale_stat_by_date()
{
    global $wp_eStore_config;
    $eStore_purchase_by_date_data = $wp_eStore_config->getValue('eStore_purchase_by_date_data_between_dates');
    foreach($eStore_purchase_by_date_data as $item){
            $item_sold_by_date_row_data .= '["'.$item['date'].'", '.$item['sale_total'].', '.$item['qty_sold'].'],';
    }	
    //print_r($eStore_purchase_by_date_data);
    ?>
    <script type="text/javascript">
    google.load("visualization", "1", {packages:["corechart"]});
    google.setOnLoadCallback(drawChart2);
    function drawChart2() {
      var data = new google.visualization.DataTable();
      data.addColumn('string', 'Date');
      data.addColumn('number', 'Sales Amount');
      data.addColumn('number', 'Quantity Sold');
      data.addRows([
        <?php echo $item_sold_by_date_row_data; ?>
      ]);

      var chart = new google.visualization.AreaChart(document.getElementById('chart_div_2'));
      chart.draw(data, {width: 650, height: 240, title: 'Sale Amount and Quantity Sold by Date', colors:['#3366CC','#9AA2B4','#FFE1C9'],
                        hAxis: {title: 'Date', titleTextStyle: {color: 'black'}}
                       });
    }
    </script>
    <div id="chart_div_2"></div>
	<?php     
}

function eStore_draw_itemisied_sale_stat($item_stats)
{
    foreach($item_stats as $item){
            $item_name_qty_sold_row_data .= '["'.htmlspecialchars($item['item_name']).'", '.$item['qty_sold'].'],';
    }
    //echo "<br />".$item_name_qty_sold_row_data;
    ?>
    <script type="text/javascript">    
      // Load the Visualization API and the piechart package.
      google.load('visualization', '1.0', {'packages':['corechart']});
      
      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(drawChart);
      
      // Callback that creates and populates a data table, 
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawChart() {

      // Create the data table.
      var data = new google.visualization.DataTable();
      data.addColumn('string', 'Products');
      data.addColumn('number', 'Numbers');
      data.addRows([
        <?php echo $item_name_qty_sold_row_data; ?>
      ]);

      // Set chart options
      var options = {'title':'Product Sale Quantity Stats',
                     'width':450,
                     'height':300};

      // Instantiate and draw our chart, passing in some options.
      var chart = new google.visualization.PieChart(document.getElementById('chart_div_1'));
      chart.draw(data, options);
    }
    </script>
    
	<div id="chart_div_1"></div>
	<?php 		
}

function eStore_itemised_sale_stat_between_dates_new($start_date,$end_date)
{
    global $wpdb;
    $wp_eStore_config = WP_eStore_Config::getInstance();
    $products_table_name = WP_ESTORE_PRODUCTS_TABLE_NAME;
    $cust_data_arr_between_dates = $wp_eStore_config->getValue('eStore_customers_data_between_dates');

    //$query = $wpdb->get_results("SELECT * FROM $sales_table_name WHERE date BETWEEN '$start_date' AND '$end_date'", OBJECT);

    $item_stats = array();
    //$item_sale_details = array();
    if (!empty($cust_data_arr_between_dates))
    {
        foreach ($cust_data_arr_between_dates as $row)
        {
            $item_id = $row['purchased_product_id'];
            $retrieved_product = $wpdb->get_row("SELECT * FROM $products_table_name WHERE id = '$item_id'", OBJECT);
            if($retrieved_product){
                $item_name = $retrieved_product->name;
            }

            $sale_price = $row['sale_amount'];
            if ($sale_price < 0){ //This is a refund
                continue;        	
            }			

            $qty_sold = $row['purchase_qty'];
            if(empty($qty_sold)){$qty_sold = 1;}
            
            //Tally up the array based on unique product IDs
            $value = $item_id;
            $key = "item_id";
            if (!eStoreMyInArray($item_stats, $value, $key))
            {
                // Add the new sale item in the array
                $item_sale_details = array('item_id' => $item_id, 'sale_price' => $sale_price, 'qty_sold' => $qty_sold, 'sale_total' => $sale_price,'item_name'=>$item_name);
                array_push($item_stats, $item_sale_details);                       	
            }
            else
            {
                //Update the qty_sold and sale amount of the existing array element
                foreach ($item_stats as $key => $item){
                    if ($item['item_id'] == $item_id){
                        $item['qty_sold'] = $item['qty_sold'] + $qty_sold;
                        $item['sale_total'] = $item['sale_total'] + $sale_price;
       	                unset($item_stats[$key]);
        	        array_push($item_stats, $item);
                    }
                }
            }
        }
    }
    $wp_eStore_config->setValue('eStore_itemized_sale_stat_between_dates',$item_stats);
    $wp_eStore_config->saveConfig();    
    return $item_stats;
}

function eStore_load_customers_data_into_array_between_dates($start_date,$end_date)
{
    global $wpdb,$wp_eStore_config;
    global $customer_table_name;
    $eStore_customers_data = array();
    $resultset = $wpdb->get_results("SELECT * FROM $customer_table_name WHERE date BETWEEN '$start_date' AND '$end_date'", OBJECT);
    foreach($resultset as $row)
    {
            $individual_customer_data = array('id' => $row->id, 
            'first_name' => $row->first_name, 
            'last_name' => $row->last_name, 
            'email_address' => $row->email_address,
            'purchased_product_id'=> $row->purchased_product_id,
            'txn_id' => $row->txn_id,
            'date' => $row->date,
            'sale_amount' => $row->sale_amount,
            'coupon_code_used' => $row->coupon_code_used,
            'purchase_qty' => $row->purchase_qty,
            'ipaddress' => $row->ipaddress,
            'product_name' => $row->product_name
            );
            array_push($eStore_customers_data, $individual_customer_data);
    }
    $wp_eStore_config->setValue('eStore_customers_data_between_dates',$eStore_customers_data);

    $eStore_customers_data_unique_txn = eStore_remove_duplicate_array_value_based_on_key($eStore_customers_data,'txn_id');	
    $wp_eStore_config->setValue('eStore_customers_data_between_dates_unique_txns',$eStore_customers_data_unique_txn);
	
    //purchase amount by date value
    $item_purchase_stats_by_date = array();
    foreach($eStore_customers_data as $row)
    {
    	$current_row_index_value = $row['date'];
    	$sale_amt = $row['sale_amount'];
    	$purchase_qty = $row['purchase_qty'];
    	
        if ($sale_amt < 0) //This is a refund
        {
                continue;        	
        }	    

        //Tally up the array based on date. The key of each array index is the date value
        $value = $row['date'];
        $key = "date";
        if (!eStoreMyInArray($item_purchase_stats_by_date, $value, $key))
        {
            if(!empty($sale_amt)){
                if(empty($purchase_qty)){$purchase_qty=1;}
                // Add the new sale item by date in the array
                $item_details = array('date' => $current_row_index_value, 'qty_sold' => $purchase_qty, 'sale_total' => $sale_amt);
                array_push($item_purchase_stats_by_date, $item_details);  
            }
        }
        else
        {
            //Add the qty_sold and sale amount for this date
            foreach ($item_purchase_stats_by_date as $key => $item)
            {
                if ($item['date'] == $current_row_index_value)
                {
                        $item['qty_sold'] = $item['qty_sold'] + $purchase_qty;
                        $item['sale_total'] = $item['sale_total'] + $sale_amt;
                        unset($item_purchase_stats_by_date[$key]);
                        array_push($item_purchase_stats_by_date, $item);
                }
            }
        }
    }
    $wp_eStore_config->setValue('eStore_purchase_by_date_data_between_dates',$item_purchase_stats_by_date);
    $wp_eStore_config->saveConfig();    
}
