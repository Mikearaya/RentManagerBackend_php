<?php 
class Employee_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	public function get_employee($id) {
		$result = $this->db->get_where('employee', array('EMPLOYEE_ID' => $id));		
		return ($result->num_rows() == 1) ? $result->row_array() : false;
	}

	public function filter_employees($filter_string = '', 
									$sort_column = 'first_name',
									 $sort_order = 'ASC', 
									 $page_index = 0, 
									 $page_size = 100) {
		$this->db->select();
		$this->db->from('employee');
		$this->db->like('first_name', $filter_string);
		$this->db->like('last_name', $filter_string);
		$this->db->like('country', $filter_string);
		$this->db->like('city', $filter_string);
		$this->db->like('phone_number', $filter_string);
		$this->db->like('sub_city', $filter_string);
		$cloned = clone $this->db;
		$offset;
		$result['total'] = $cloned->count_all_results();
		if($page_index === 0) {
			$offset = 0;
		} else {
			$offset = $page_index * $page_size;
		}
		$this->db->limit($page_size, $offset);
		$query = $this->db->get();

		$result['employees'] = $query->result_array();

		return $result;

	}

	public function add_employee($new_employee) {
		$this->db->insert('employee', $new_employee);
		if($this->db->affected_rows() == 1) {
			$new_employee['EMPLOYEE_ID'] = $this->db->insert_id();
			return $new_employee;
		} else {
			return false;
		}
	}

	public function update_employee($id, $updated_employee) {
		$this->db->where('EMPLOYEE_ID', $id);
		$this->db->update('employee', $updated_employee);
		if($this->db->affected_rows() === 1) {
			$updated_employee['EMPLOYEE_ID'] = $id;
			return $updated_employee;
		} else {
			return false;
		}
	}


	public function delete_employee($id) {
		$this->db->where('EMPLOYEE_ID', $id);
		return $this->db->delete('employee');
	}
}

?>
