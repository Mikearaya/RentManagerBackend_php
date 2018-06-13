<?php

class Partner_model extends CI_Model {

	public function __construct() {
		parent::__construct();
		$this->load->database();
	}

	public function get_partner($id = NULL) {
		$result = [];
		if(!is_null($id)) {
		$result =	$this->db->get_where('car_owner', array('OWNER_ID' => $id ));
		return $result->row_array();
		} else {
			$result = $this->db->get('car_owner');
			return $result->result_array();
		}
	}

	public function add_partner($partner) {
		return $this->db->insert('car_owner', $partner);
	}
	public function update_partner($partner, $id) {
		$this->db->where('OWNER_ID', $id);
		return $this->db->update('car_owner', $partner);
	}
}
?>
