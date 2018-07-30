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
		COUNT(CASE WHEN 
							IFNULL(DATE_ADD(return_date, INTERVAL ex_r.extended_days DAY), return_date) < NOW() AND UPPER(r.status) = 'RENTED' THEN 1 END) as 'overdo_rents', 
							COUNT(CASE WHEN 
							IFNULL(DATE_ADD(return_date, INTERVAL ex_r.extended_days DAY), return_date) > NOW() THEN 1 END) as 'rented', 
							COUNT(CASE WHEN
								 (	status = 'RETURNED'  OR ISNULL(status) ) THEN 1 END)  as 'available',
								 COUNT(CASE WHEN
								 IFNULL(DATE(DATE_ADD(return_date, INTERVAL ex_r.extended_days DAY)), DATE(return_date)) = DATE(NOW()) THEN 1 END)  as 'today_returns',
								 COUNT(CASE WHEN
								 IFNULL(WEEK(DATE_ADD(return_date, INTERVAL ex_r.extended_days DAY)), WEEK(return_date)) = WEEK(NOW()) THEN 1 END)  as 'this_week_return' ");
		$this->db->from('vehicle');
		$this->db->join("(SELECT * FROM rent GROUP BY VEHICLE_ID) as  r", 'r.VEHICLE_ID = vehicle.VEHICLE_ID', 'left');
		$this->db->join("(SELECT RENT_ID, SUM(extended_days) AS 'extended_days' 
								FROM extended_rent
								GROUP BY RENT_ID) AS ex_r" , 'r.RENT_ID = ex_r.RENT_ID', 'left');
	
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
		$this->db->select("IFNULL(sum(DATEDIFF(return_date, start_date) * rent.rented_price) + SUM(extended_days * ex_r.rented_price), 
							SUM(DATEDIFF(return_date, start_date) * rent.rented_price) ) as 'total_amount', 
										SUM(rent_payment.payment_amount) AS 'paid_amount', 
										IFNULL( ((DATEDIFF(return_date, start_date) * rent.rented_price) + (SUM(extended_days * ex_r.rented_price) - rent_payment.payment_amount)),
													((DATEDIFF(return_date, start_date) * rent.rented_price) -  SUM(rent_payment.payment_amount  )))  AS 'remaining_amount'");	
		$this->db->from('rent');
		$this->db->join("(SELECT RENT_ID,  payment_amount from rent_payment group by RENT_ID) AS rent_payment", "rent.RENT_ID =  rent_payment.RENT_ID" );
		$this->db->join( '(SELECT RENT_ID, SUM(extended_days) as extended_days, rented_price FROM extended_rent GROUP BY RENT_ID ) AS ex_r', 
													'rent.RENT_ID =  ex_r.RENT_ID', "left");
													
		$result = $this->db->get();
		return $result->row_array();
	}

	function rent_summary() {
		$this->db->select("COUNT(*) as 'total',  
							COUNT(CASE WHEN 
							IFNULL(DATE_ADD(return_date, INTERVAL ex_r.extended_days DAY), return_date) > NOW() AND UPPER(r.status) = 'RENTED' THEN 1 END) as 'overdo_rents', 
							COUNT(CASE WHEN 
							IFNULL(DATE_ADD(return_date, INTERVAL ex_r.extended_days DAY), return_date) > NOW() THEN 1 END) as 'rented', 
							COUNT(CASE WHEN
								 IFNULL(DATE_ADD(return_date, INTERVAL ex_r.extended_days DAY), return_date) < NOW() THEN 1 END)  as 'available',
								 COUNT(CASE WHEN
								 IFNULL(DATE(DATE_ADD(return_date, INTERVAL ex_r.extended_days DAY)), DATE(return_date)) = DATE(NOW()) THEN 1 END)  as 'today_returns',
								 COUNT(CASE WHEN
								 IFNULL(WEEK(DATE_ADD(return_date, INTERVAL ex_r.extended_days DAY)), WEEK(return_date)) = WEEK(NOW()) THEN 1 END)  as 'this_week_return' ");
		$this->db->from('vehicle');
		$this->db->join('rent r', 'r.VEHICLE_ID = vehicle.VEHICLE_ID');
		$this->db->join("(SELECT RENT_ID, SUM(extended_days) AS 'extended_days' 
								FROM extended_rent
								GROUP BY RENT_ID) AS ex_r" , 'r.RENT_ID = ex_r.RENT_ID', 'left');
		$result = $this->db->get();
		return $result->row();
	}
}


?>
