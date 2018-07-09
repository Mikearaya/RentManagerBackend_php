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
		$result = $this->db->insert('customer', $customer);
		
			if($this->db->affected_rows() == 1 ) {
				 $customer['CUSTOMER_ID'] =  $this->db->insert_id();
				 return $customer;
			 } else {
				return false;
			 }
	}

	public function update_customer( $id, $customer) {

			$this->db->where('CUSTOMER_ID', $id );
		$customer['CUSTOMER_ID'] = $id;
			$result = $this->db->update('customer', $customer);
		if($this->db->affected_rows() == 1 ) {
				$customer['CUSTOMER_ID'] =  $id;
			return $customer;
		 } else {
			return false;
		 }
	}

	public function delete_customers($id) {
		$ids = [];
		$result = NULL;

		foreach ($id as $key => $value) {
			$this->db->where('CUSTOMER_ID', $value);
			$result = $this->db->delete('customer');
			if(!$result) {
				break;
			}
		}
			if($result) {
				
				return true;
			} else {
				return false;
			}
			
	
	}
	public function delete_customer($id) {
		$ids = [];
		$result = NULL;
			$this->db->where('CUSTOMER_ID', $id);
			$result = $this->db->delete('customer');
			if($result) {				
				return true;
			} else {
				return false;
			}			
	
	}


}
?>
