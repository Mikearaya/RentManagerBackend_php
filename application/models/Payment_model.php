<?php 

class Payment_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_rent_payment($catagory, $filter_string = '', $sort_column = '', $sort_order = 'desc', $page_index = 0, $page_size = 20) {
		$this->db->select("rent.RENT_ID, 
		SUM(PAYMENT_ID) as 'number_of_payment',  
        COUNT(*) as 'number_of_payments',
		IFNULL((DATEDIFF(return_date, start_date) * rent.rented_price ) + ex_r.extended_rent_payment, 
				(DATEDIFF(return_date, start_date) * rent.rented_price) ) as 'total_amount', 
					IFNULL(total_extended_days, 'NONE') as 'total_extended_days',
										IFNULL((DATEDIFF(return_date, start_date) * (rent.rented_price - rent.owner_renting_price )) + comission_from_extended, 
										(DATEDIFF(return_date, start_date) * (rent.rented_price - rent.owner_renting_price)))  as 'total_comission', 
							SUM(rent_payment.payment_amount) AS 'paid_amount', 
							IFNULL( ((DATEDIFF(return_date, start_date) * rent.rented_price) + (SUM(extended_days * ex_r.rented_price) - rent_payment.payment_amount)),
													((DATEDIFF(return_date, start_date) * rent.rented_price) -  SUM(rent_payment.payment_amount  )))  AS 'remaining_amount',
							IFNULL((DATEDIFF(return_date, start_date) + extended_days),
													  DATEDIFF(return_date, start_date)) as total_days, rent_payment.payment_amount,
							start_date,
							IFNULL(DATE_ADD(return_date , INTERVAL sum(extended_days) DAY), return_date) as 'return_date'  ,
							plate_code, 
							plate_number, 
							CONCAT(c.first_name,' ', c.last_name) as 'rented_by'");
		$this->db->from('rent');
		$this->db->join('customer c', 'c.CUSTOMER_ID = rent.CUSTOMER_ID', 'left');
        $this->db->join('vehicle', 'vehicle.VEHICLE_ID = rent.VEHICLE_ID', 'left');
        $this->db->join("(SELECT RENT_ID, PAYMENT_ID, payment_amount as payment_amount from rent_payment) AS rent_payment", "rent.RENT_ID =  rent_payment.RENT_ID", "left" );
		$this->db->join( "(SELECT RENT_ID, SUM(extended_days) as 'total_extended_days', 
		SUM(rented_price * extended_days) as 'extended_rent_payment', 
		extended_days,
		SUM( ((rented_price - owner_renting_price ) * extended_days )) as 'comission_from_extended', 
		owner_renting_price, rented_price FROM extended_rent ) AS ex_r", 
													'rent.RENT_ID =  ex_r.RENT_ID', "left");
        $this->db->group_by('rent.RENT_ID');

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
