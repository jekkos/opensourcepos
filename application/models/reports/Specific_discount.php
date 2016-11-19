<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Report.php");

class Specific_discount extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function create(array $inputs)
	{
		//Create our temp tables to work with the data in our report
		$this->Sale->create_temp_table($inputs);
	}
	
	public function getDataColumns()
	{
		return array('summary' => array($this->lang->line('reports_sale_id'), $this->lang->line('reports_date'), $this->lang->line('reports_quantity'), $this->lang->line('reports_sold_to'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'), /*$this->lang->line('reports_profit'),*/ $this->lang->line('reports_payment_type'), $this->lang->line('reports_comments')),
					 'details' => array($this->lang->line('reports_name'), $this->lang->line('reports_category'), $this->lang->line('reports_serial_number'), $this->lang->line('reports_description'), $this->lang->line('reports_quantity'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'), /*$this->lang->line('reports_profit'),*/ $this->lang->line('reports_discount'))
		);		
	}
	
	public function getData(array $inputs)
	{
		$this->db->select('sale_id, MAX(sale_date) AS sales_date, SUM(quantity_purchased) AS items_purchased, 
		MAX(customer_name) AS customer_name, SUM(subtotal) AS subtotal, SUM(total) AS total, SUM(tax) AS tax, SUM(cost) AS cost, 
		SUM(profit) AS profit, MAX(payment_type) AS payment_type, MAX(comment) AS comment');
		$this->db->from('sales_items_temp AS sales_items_temp');
		$this->db->where('discount_percent >=', $inputs['discount']);

		if ($inputs['sale_type'] == 'sales')
		{
			$this->db->where('quantity_purchased > 0');
		}
		elseif ($inputs['sale_type'] == 'returns')
		{
			$this->db->where('quantity_purchased < 0');
		}

		$this->db->group_by('sale_id');
		$this->db->order_by('MAX(sale_date)');

		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		$data['details'] = array();
		
		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select('name, serialnumber, category, description, quantity_purchased, subtotal, total, tax, cost, profit, discount_percent');
			$this->db->from('sales_items_temp');
			$this->db->where('sale_id', $value['sale_id']);
			$data['details'][$key] = $this->db->get()->result_array();
		}

		return $data;
	}
	
	public function getSummaryData(array $inputs)
	{
		$this->db->select('SUM(subtotal) AS subtotal, SUM(total) AS total, SUM(tax) AS tax, SUM(cost) AS cost, SUM(profit) AS profit');
		$this->db->from('sales_items_temp');
		$this->db->where('discount_percent >=', $inputs['discount']);
			
		if ($inputs['sale_type'] == 'sales')
		{
			$this->db->where('quantity_purchased > 0');
		}
		elseif ($inputs['sale_type'] == 'returns')
		{
			$this->db->where('quantity_purchased < 0');
		}

		return $this->db->get()->row_array();
	}
}
?>