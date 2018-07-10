<?php

class Partner_model extends CI_Model {

	public function __construct() {
		parent::__construct();
		$this->load->database();
	}

	public function get_partner($id = NULL, $filter_string, $page_size, $page_number, $sort_order, $sort_column) {

		$result = [];
		if(is_null($id)) {
			$this->db->like('first_name', $filter_string);
			$this->db->or_like('last_name', $filter_string);
			$this->db->or_like('mobile_number', $filter_string);
			$this->db->or_like('city', $filter_string, 'after');
			$this->db->or_like('sub_city', $filter_string, 'after');
			$this->db->or_like('wereda', $filter_string, 'after');
			$this->db->order_by($sort_column, $sort_order);

			if($page_number === 0) {
				$start = 0;
				$end = $page_size;
			} else {
				$start = $page_number * $page_size;
				$end = $start + $page_size;
			}

			$this->db->limit($page_size , $start);
			$result_set = $this->db->get('vehicle_owner');
			$result['total'] = $this->db->count_all('vehicle_owner');
			$result['owners'] = $result_set->result_array();
			return $result;
		} else {
				$result =	$this->db->get_where('vehicle_owner', array('OWNER_ID' => $id ));
			return $result->row_array();
		} 
	}

	public function add_partner($new_partner) {
		
		$partner = $this->set_data_model($new_partner);
		$this->db->insert('vehicle_owner', $partner);

		if($this->db->affected_rows() == 1) {
			$partner['OWNER_ID'] = $this->db->insert_id();  
			return $partner;
		} else {
			return false;
		}
	}
	public function update_partner($partner, $id) {
		$this->db->where('OWNER_ID', $id);
		return $this->db->update('vehicle_owner', $partner);
	}

	private function set_data_model($owner) {
		$data_model = array(
			'first_name' => $owner['first_name'],
			'last_name' => $owner['last_name'],
			'mobile_number' => $owner['mobile_number'],
			'city' => $owner['city'],
			'sub_city' => $owner['sub_city'],
			'wereda' => $owner['wereda'],
			'house_number' => $owner['house_number']
		);

		return $data_model;
	}
}
?>
