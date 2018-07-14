<?php

class Customer_Model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	public function get_customer($id) {
		$result = $this->db->get_where('customer', array('CUSTOMER_ID' => $id));
		if($result->num_rows() !== 1 ) {
			return 404;
		} else { 
			return $result->row_array();
		}

	}

	public function get_all_customer($id) {
		$result = $this->db->get('customer');
			return $result->result_array();

	}

	public function filter_customers($filter_string = '', $sort_column = '', $sort_order = 'ASC', $page_number = 0, $page_size = 20) {
		$this->db->like('first_name', $filter_string);
		$this->db->or_like('last_name', $filter_string);
		$this->db->or_like('mobile_number', $filter_string);
		$this->db->or_like('other_phone', $filter_string);
		$this->db->or_like('country', $filter_string);
		$this->db->or_like('city', $filter_string);
		$this->db->or_like('nationality', $filter_string);
		$this->db->or_like('passport_number', $filter_string);
		$this->db->or_like('driving_licence_id', $filter_string);
		$this->db->order_by($sort_column, $sort_order);



		if($page_number === 0) {
			$start = 0;
			$end = $page_size;
		} else {
			$start = $page_number * $page_size;
			$end = $start + $page_size;
		}
		$this->db->limit($page_size , $start);
			
				$result_set = $this->db->get('customer');
				$result['total'] = $this->db->count_all('customer');
				$result['customers'] = $result_set->result_array();
		
			return $result;


	}


	public function add_customer($customer) {
		$new_customer = $this->set_customer_data_model($customer);
		$result = $this->db->insert('customer', $new_customer);
		
			if($this->db->affected_rows() == 1 ) {
				 $new_customer['CUSTOMER_ID'] =  $this->db->insert_id();
				 return $new_customer;
			 } else {
				return false;
			 }
	}

	public function update_customer( $id, $customer) {
		$updated_customer = $this->set_customer_data_model($customer);
			$this->db->where('CUSTOMER_ID', $id );
		$updated_customer['CUSTOMER_ID'] = $id;
			$result = $this->db->update('customer', $updated_customer);
		if($this->db->affected_rows() == 1 ) {
				$updated_customer['CUSTOMER_ID'] =  $id;
			return $updated_customer;
		 } else {
			return false;
		 }
	}

	public function delete_customers($id) {
		$ids = [];
		$result = NULL;
	
		foreach($id as $key => $value) {
			$ids[] = $value;
		}

		$this->db->where_in('CUSTOMER_ID', $ids);
		$result = $this->db->delete('customer');
		return ($result) ? true : false;
	}
	
	public function delete_customer($id) {
		$ids = [];
		$result = NULL;
			try {
					$this->db->where('CUSTOMER_ID', $id);
					$result = $this->db->delete('customer');

				return ($this->db->affected_rows() > 0) ? true : false;
	
			} catch (Exception $e) {
				return false;
			}
	}
	private function set_customer_data_model($customer) {
		$data_model = array(
			'first_name' => $customer['first_name'],
			'last_name' => $customer['last_name'],
			'country' => $customer['country'],
			'city' => $customer['city'],
			'mobile_number' => $customer['mobile_number'],
			'house_no' => $customer['house_no'],
			'driving_licence_id' => $customer['driving_licence_id'],
		);

		if(isset($customer['nationality']) && trim($customer['nationality'])) {
			$data_model['nationality'] = trim($customer['nationality']);
			$data_model['passport_number'] = trim($customer['passport_number']);
			$data_model['hotel_name'] = trim($customer['hotel_name']);
			$data_model['hotel_phone'] = trim($customer['hotel_phone']);

		}
		if(isset($customer['other_phone']) && trim($customer['other_phone'])) {
			$data_model['other_phone'] = trim($customer['other_phone']);
		}

		return $data_model;
	}


}
?>
