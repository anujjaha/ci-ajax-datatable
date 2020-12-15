<?php

/**
 * The file that defines the datatable class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://rahulasr.com/
 * @since      1.0.0
 *
 * @package    Datatables
 */

/**
 * The core plugin class.
 *
 * This is used to create datatables without any extra code, developer sepcify
 * table, columns.
 *
 *
 * @since      1.0.0
 * @package    Datatable Library
 * @author     Rahul Sharma <rahulsharma841990@outlook.com>
 */

class Datatables{
  /*
  *
  * Save class instance
  *
  */
  private $CI;

  /*
  *
  * Store the table name for generate table
  *
  */

  private $table;

  /*
  *
  * Store columns to display in datatable
  *
  */

  private $columns = '*';

  /*
  *
  * Store columns for searchable
  *
  */

  private $searchable;

  protected $isCallback = false;

  function __construct($config){

    /*
    *
    * Create instance of class
    *
    */
    $this->CI = &get_instance();

    /*
    *
    * Get table name from config
    *
    */
    $this->table = $config['table'];

    /*
    *
    * Get columns from config and store
    * in private variable.
    *
    */

    $this->columns = $config['columns'];

    /*
    *
    * Get column's name for make searchable
    *
    */
    $this->searchable = $config['searchable'];
    
    $this->limit = $config['limit'] ?? 10;
    
    $this->start = $config['start'] ?? 0;

    $this->callback = null;
    $this->whereConditions = [];
  }

  function setJoin($table = null, $condition = null, $left = 'left')
  {
    if($table && $condition)
    {
      $this->CI->db->join($table, $condition, $left);
      return $this;
    }
  }

  function setColumns($columns = array())
  {
    if($columns && count($columns))
    {
      $this->CI->db->select(implode(',', $columns));
      return $this;
    }
  }

  function setPreAction($callback = null) {
    $this->isCallback = true;
    if($callback) {
      $this->callback = $callback;
    }
    
    return $this;
  }

  function setWhere($conditions = array())
  {
    if(is_array($conditions) && count($conditions))
    {
      $this->whereConditions = $conditions;
    }
    return $this;
  }
  /*
  *
  * Generate the datatable
  *
  */
  function generate(){

    //Getting all posted data
    $postData = $this->CI->input->post();

  
  
    $length = $postData['length'] ?? $this->limit;
    $start = $postData['start'] ?? $this->start;

    
    //Getting data with limit and offset
    $this->CI->db->limit($length, $start)->order_by(
                      $this->columns[$postData['order'][0]['column']],$postData['order'][0]['dir']
                    );

    //check if search is not empty
    if(!empty($postData['search']['value'])){
      $index = 1;
      foreach($this->searchable as $key => $value){
        if($index == 1){

          $this->CI->db->where($value. " LIKE '%".$postData['search']['value']."%'");
        }else{

          $this->CI->db->or_where($value. " LIKE '%".$postData['search']['value']."%'");
        }
        $index++;
      }

      if(is_array($this->whereConditions) && count($this->whereConditions)) {
        $this->CI->db->where($this->whereConditions);
      }
      //get all the filtered rows
      $Query = $this->CI->db->get($this->table);
      $parseData = $Query->result();
      $filtered = count($parseData);
    }else{

      if(is_array($this->whereConditions) && count($this->whereConditions)) {
        $this->CI->db->where($this->whereConditions);
      }
      //if not searched any value
      $Query = $this->CI->db->get($this->table);
      //logQuery(false);
      $parseData = $Query->result();
    }

    //Generating array according to datatable
    $ArrayData = [];
    foreach($parseData as $key => $value){

      $dataArray = [];

      foreach($this->columns as $cKey => $cValue){

        if($this->isCallback)
        {
          $dataArray[] = call_user_func($this->callback, $value, $cValue, $value->{$cValue}); 
        }
        else
        {
          $dataArray[] = $value->{$cValue}; 
        }
      }

      $ArrayData[] = $dataArray;
    }


    //Get the total number of records
    if(is_array($this->whereConditions) && count($this->whereConditions)) {
      $this->CI->db->where($this->whereConditions);
    }
    $Query = $this->CI->db->get($this->table);
    $totalData = $Query->num_rows();
    $filtered = $filtered ?? $totalData;
    $jsonData = array(
              'draw' => (int)$postData['draw'],
              'recordsTotal' => (int)$totalData,
              'recordsFiltered' => (int)$filtered,
              'data' => $ArrayData
             );
    echo json_encode($jsonData);
  }
}