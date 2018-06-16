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

			$this->db->limit(1000 , $start);
			
				$result_set = $this->db->get('vehicle_owner');
		
			return $result_set->result_array();
		} else {
				$result =	$this->db->get_where('vehicle_owner', array('OWNER_ID' => $id ));
			return $result->row_array();
		} 
	}

	public function add_partner($partner) {
		return $this->db->insert('vehicle_owner', $partner);
	}
	public function update_partner($partner, $id) {
		$this->db->where('OWNER_ID', $id);
		return $this->db->update('vehicle_owner', $partner);
	}
}
?>
