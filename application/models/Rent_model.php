<?php

class Rent_model extends CI_Model {

    function __construct() {
		parent::__construct();
		$this->load->database();
	}
	
	public function get_rent($id = NULL, $filter_string, $page_size, $page_number, $sort_order, $sort_column) {

		$result = [];
		$this->db->select("RENT_ID, CONCAT(first_name,'  ',last_name ) as rented_by, CONCAT(plate_code,'-',plate_number) as plate_number,
		start_date, return_date");
		$this->db->from('rent');
		$this->db->join('vehicle', 'rent.VEHICLE_ID = vehicle.VEHICLE_ID', 'left');
		$this->db->join('customer', 'customer.CUSTOMER_ID = rent.CUSTOMER_ID', 'left');
		if(is_null($id)) {
			$this->db->like('CONCAT(first_name, '.'last_name)', $filter_string);
			$this->db->or_like('plate_number', $filter_string);
			$this->db->order_by($sort_column, $sort_order);

			if($page_number === 0) {
				$start = 0;
				$end = $page_size;
			} else {
				$start = $page_number * $page_size;
				$end = $start + $page_size;
			}

			$this->db->limit(1000 , $start);
			
				$result_set = $this->db->get();
		
			return $result_set->result_array();
		} else {
				$this->db->where('RENT_ID',$id );
				$result = $this->db->get();
			return $result->row_array();
		} 
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
