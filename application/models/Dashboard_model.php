<?php

class Dashboard_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	function year_rent_count_by_month($type = 'ALL') {
		$this->db->select("COUNT(RENT_ID) as 'total', MONTH(start_date) as 'month' ");
		$this->db->from('rent');
		$this->db->where('YEAR(start_date) =  YEAR(CURRENT_TIMESTAMP)');
		
		if(strtoupper($type) === 'ALL')		{
		$this->db->where('YEAR(start_date) =  YEAR(CURRENT_TIMESTAMP)');
		} else {
			$this->db->where('MONTH(start_date) =  MONTH(CURRENT_TIMESTAMP)');
		}
		$this->db->group_by('MONTH(start_date)');
		$this->db->order_by('MONTH(start_date)', 'ASC');
		
		$result = $this->db->get();
		
		return $result->result_array();
	}

	function number_of_customers() {
		$this->db->select("COUNT(CUSTOMER_ID) as 'total' ");
		$this->db->from('customer');
		$result = $this->db->get();
		return $result->row();
	}

	function number_of_vehicles() {
		$this->db->select("COUNT(*) as 'total',  
							COUNT(CASE WHEN return_date > NOW() THEN 1 END) as 'rented', 
							COUNT(CASE WHEN return_date <= NOW() THEN 1 END)  as 'available' ");
		$this->db->from('vehicle');
		$this->db->join('rent r', 'r.VEHICLE_ID = vehicle.VEHICLE_ID');
		$result = $this->db->get();
		return $result->row();
	}

	function number_of_partners() {
		$this->db->select("COUNT(OWNER_ID) as 'total' ");
		$this->db->from('vehicle_owner');
		$result = $this->db->get();
		return $result->row();

	}

	function rent_payment_summary() {
		$this->db->select("COUNT(*), IFNULL(sum(DATEDIFF(return_date, start_date) * rent.rented_price) + SUM(extended_days * extended_rent.rented_price), 
							SUM(DATEDIFF(return_date, start_date) * rent.rented_price) ) as 'total_amount', 
										SUM(rent_payment.payment_amount) AS 'paid_amount', 
							IFNULL( (SUM(DATEDIFF(return_date, start_date) * rent.rented_price) + 
									(SUM(extended_days * extended_rent.rented_price) - SUM(rent_payment.payment_amount))),
									(SUM(DATEDIFF(return_date, start_date) * rent.rented_price) -  SUM(rent_payment.payment_amount  )))  AS 'remaining_amount'");	
		$this->db->from('rent');
		$this->db->join('rent_payment', 'rent_payment.RENT_ID = rent.RENT_ID');
		$this->db->join( '(SELECT RENT_ID, extended_days, rented_price FROM extended_rent GROUP BY RENT_ID ) AS extended_rent', 
													'rent.RENT_ID =  extended_rent.RENT_ID', "left");
													
		$result = $this->db->get();
		return $result->row_array();
	}
}


?>
