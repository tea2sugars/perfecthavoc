<?php
//TODO - refactor these definitions and use the ones from main eStore constants
global $wpdb;
define('WP_ESTORE_DOWNLOAD_LINKS_TABLE_NAME', $wpdb->prefix . "wp_eStore_download_links_tbl");
define('WP_ESTORE_DB_PRODUCTS_TABLE_NAME', $wpdb->prefix . "wp_eStore_tbl");
define('WP_ESTORE_DB_CUSTOMERS_TABLE_NAME', $wpdb->prefix . "wp_eStore_customer_tbl");

class WP_eStore_Db_Access
{
   function  __construct() {
      die();
   }
   function WP_eStore_Db_Access()
   {
      die();
   }
   function find($inTable, $condition)
   {
      global $wpdb;

      if(empty($condition))
      {
          return null;
      }
      $resultset = $wpdb->get_row("SELECT * FROM $inTable WHERE $condition", OBJECT);
      return $resultset;
   }
   function findAll($inTable, $condition=null, $orderby=null)
   {
      global $wpdb;
      $condition = empty ($condition)? '' : ' WHERE ' .$condition;
      $condition .= empty($orderby)? '': ' ORDER BY ' . $orderby;
      $resultSet = $wpdb->get_results("SELECT * FROM $inTable $condition ", OBJECT);
      return $resultSet;
   }
   function findAllArray($inTable, $condition=null, $orderby=null)
   {
      global $wpdb;
      $condition = empty ($condition)? '' : ' WHERE ' .$condition;
      $condition .= empty($orderby)? '': ' ORDER BY ' . $orderby;
      $resultSet = $wpdb->get_results("SELECT * FROM $inTable $condition ", ARRAY_A);
      return $resultSet;
   }   
   function delete($fromTable, $condition)
   {
      global $wpdb;
      $resultSet = $wpdb->query("DELETE FROM $fromTable WHERE $condition ");
      return $resultSet;
   }
   function insert($inTable, $fields)
   {
      global $wpdb;
      $fieldss = '';
      $valuess = '';
      $first = true;
      foreach($fields as $field=>$value)
      {
         if($first)
            $first = false;
         else
         {
            $fieldss .= ' , ';
            $valuess .= ' , ';
         }
         $fieldss .= " $field ";
         $valuess .= " '" .esc_sql($value)."' ";
      }

      $query = " INSERT INTO $inTable ($fieldss) VALUES ($valuess)";

      $results = $wpdb->query($query);
      return $results;
   }
   function update($inTable, $condition, $fields)
   {
      global $wpdb;
      $query = " UPDATE $inTable SET ";
      $first = true;
      foreach($fields as $field=>$value)
      {
         if($first) $first = false; else $query .= ' , ';
         $query .= " $field = '" . esc_sql($value) ."' ";
      }

      $query .= empty($condition)? '': " WHERE $condition ";
      $results = $wpdb->query($query);
      return $results;
   }
}
?>