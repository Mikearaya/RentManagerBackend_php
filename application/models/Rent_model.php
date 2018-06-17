<?php

class Rent_model extends CI_Model {

    function __construct() {
		parent::__construct();
		$this->load->database();
	}
	
	public function insert_rent($rent_detail) {
		$customer = $this->set_customer_data_model($rent_detail['customer']);
		$rent = $this->set_rent_data_model($rent_detail);

		$this->db->insert('customer', $customer);
		$customer_id = $this->db->insert_id();
			if($customer_id) {
				$rent['CUSTOMER_ID'] = $customer_id;
				$this->db->insert('rent', $rent);
				if($this->db->affected_rows() > 0) {
					return true;
				} else {
					return false;
				}		
			} else {
			return false;
		}
	}


	private function set_rent_data_model($rent) {
		$rent_data_model = array(
			'VEHICLE_ID' => $rent['VEHICLE_ID'],
			'start_date' => $rent['start_date'],
			'return_date' => $rent['return_date'],
			'owner_renting_price' => $rent['owner_renting_price'],
			'initial_payment' => $rent['initial_payment'],
			'owner_renting_price' => $rent['owner_renting_price'],
			'rented_price' => $rent['rented_price']
		);
		return $rent_data_model;
	}

	private function set_customer_data_model($customer) {

			$customer_data_model = array(
				'first_name' => $customer['first_name'],
				'last_name' => $customer['last_name'],
				'id_type' => $customer['id_type'],
				'id_number' => $customer['id_number'],
				'mobile_number' => $customer['mobile_number'],
				'nationality' => $customer['nationality'],
				'country' => $customer['country'],
				'city' => $customer['city'],
				'house_no' => $customer['house_no']
			);

				if(trim($customer['other_phone'])) {
					$customer_data_model['other_phone'] = $customer['other_phone'];
				}
		return $customer_data_model;
	}


}

?>
