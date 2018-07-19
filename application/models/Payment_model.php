<?php 

class Payment_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_rent_payment($catagory, $filter_string = '', $sort_column = '', $sort_order = 'desc', $page_index = 0, $page_size = 20) {
		$this->db->select("rent.RENT_ID, 
							PAYMENT_ID,  
							IFNULL((DATEDIFF(return_date, start_date) * rent.rented_price) + SUM(extended_days * extended_rent.rented_price), 
													(DATEDIFF(return_date, start_date) * rent.rented_price) ) as 'total_amount', 
							SUM(rent_payment.payment_amount) AS 'paid_amount', 
							IFNULL( ((DATEDIFF(return_date, start_date) * rent.rented_price) + (SUM(extended_days * extended_rent.rented_price) - SUM(rent_payment.payment_amount))),
													((DATEDIFF(return_date, start_date) * rent.rented_price) -  SUM(rent_payment.payment_amount  )))  AS 'remaining_amount',
							IFNULL((DATEDIFF(return_date, start_date) + sum(extended_days)),
							 						 DATEDIFF(return_date, start_date)) as total_days,
							start_date,
							DATE_ADD(return_date , INTERVAL sum(extended_days) DAY) as 'return_date'  ,
							plate_code, 
							plate_number, 
							CONCAT(c.first_name,' ', c.last_name) as 'rented_by' ");
		$this->db->from('rent');
		$this->db->join('customer c', 'c.CUSTOMER_ID = rent.CUSTOMER_ID', 'left');
        $this->db->join('vehicle', 'vehicle.VEHICLE_ID = rent.VEHICLE_ID', 'left');
        $this->db->join(" rent_payment", "rent.RENT_ID =  rent_payment.RENT_ID", "left" );
		$this->db->join( '(SELECT RENT_ID, extended_days, rented_price FROM extended_rent GROUP BY RENT_ID ) AS extended_rent', 
													'rent.RENT_ID =  extended_rent.RENT_ID', "left");
        $this->db->group_by('rent.rent_id');

        if(strtoupper(trim($catagory)) == 'PAID') {
            $this->db->having('remaining_amount =' , 0);
        }
        else if(strtoupper(trim($catagory)) == 'remaining') {
            $this->db->having('remaining_amount >' , 0);
        }
		
		if($page_index === 0) {
			$start = 0;
			$end = $page_size;
		} else {
			$start = $page_index * $page_size;
			$end = $start + $page_size;
		}
		$cloned = clone $this->db;
	
		$this->db->limit($page_size , $start);
		$result_set = $this->db->get();
		$result['total'] = $cloned->count_all_results();
	
		$result['payments'] = $result_set->result_array();
		return $result;


	}
	

	public function add_payment($new_payment) {
		try {
			
			$payment = $this->set_payment_data_model($new_payment);
			$result = $this->db->insert('rent_payment', $payment);
		
			if($this->db->affected_rows() > 0 ) {
				$payment['PAYMENT_ID'] = $this->db->insert_id();
				return $payment;
			} else {
				return false;
			}
		} catch(Exception $e) {
			return false;
		}

	}

	private function set_payment_data_model($payment) {
		$dataModel = array(
			'RENT_ID' => $payment['RENT_ID'],
			'payment_amount' => $payment['payment_amount'] 
		);

		return $dataModel;
	}
}

?>
