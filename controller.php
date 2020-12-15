<?php

/**
 * Contract
 *
 */
class Test extends CI_Controller {

    /**
     * Construct
     *
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Record List
     *
     * @return JsonResponse
     */
    public function recordList()
    {
        $this->load->library('datatables', [
            'table'         => 'tablename',
            'columns'       => ['column1', 'column2', 'column3', 'column4', 'column5'],
            'searchable'    => ['column1', 'column2']
        ]);

        header('Content-Type: application/json');

        return $this->datatables
            ->setJoin('table1', 'table1.id = tablename.testId')
            ->setColumns([
                'tablename.*',
                'table1.column5',
            ])
            ->setPreAction(function($record, $column, $value){
                return $this->getCustomColumns($record, $column, $value);
            })
            ->setWhere([
                'tablename.field1' => 'test value'
            ])
            ->generate();
    }

    /**
     * Get Custom Columns
     *
     * @param object $record
     * @param string $column
     * @param string $value
     * @param return $string
     */ 
    function getCustomColumns($record, $column, $value)
    {
        if($column == 'action') {
            return $this->load->view('template/button/staff-action', [
                'record' => $record
            ], TRUE);;
        }

        return $value;
    }
}