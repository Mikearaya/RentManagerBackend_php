<?php


class Vehicle_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	public function get_vehicle($id = NULL) {
		
		if(is_null($id)) {
				$result_set = $this->db->get('vehicle');
			return $result_set->result_array();		
		} else {
			$this->db->where('VEHICLE_ID', $id);
			$result_set = $this->db->get('vehicle');	
			return $result_set->row_array();
		}
	}

	public function filter_vehicle($owner_id  = NULL, $catagory = 'all', $filter_string, $page_number, $page_size, $sort = 'ASC', $sort_column = 'VEHICLE_ID') {
		if($page_number == 0) {
			$start = 0;
			$end = $page_size;
		} else {
			$start = $page_number * $page_size;
			$end = $start + $page_size;
		}
		$result_set = [];	
		if($owner_id) {
			$result_set = $this->db->get_where('vehicle', array('OWNER_ID' => $owner_id));
		} else {
			
			$like = "(make LIKE '%".$filter_string."%'  OR 
					model LIKE '%".$filter_string."%' OR
					year_made LIKE '%".$filter_string."%' OR
					color LIKE '%".$filter_string."%' OR
					type LIKE '%".$filter_string."%')";
			$this->db->where($like);
			if(strtoupper(trim($catagory)) == 'RENTED') {
				$this->db->where("status = 'RENTED'");	
			} else if(strtoupper(trim($catagory)) == 'AVAILABLE') {
				$this->db->where("status = 'RETURNED' OR ISNULL(status) ");
			}
					
			$this->db->join('(SELECT VEHICLE_ID as R_VEHICLE_ID, status FROM rent ) as rent', 'rent.R_VEHICLE_ID = vehicle.VEHICLE_ID', 'left');
			$this->db->group_by('vehicle.VEHICLE_ID');
			$this->db->order_by($sort_column, $sort);
		

			$cloned = clone $this->db;
	
			
			$result['total'] = $cloned->count_all_results('vehicle');
	
			$result_set = $this->db->get('vehicle');
		
			}
				$result['vehicles'] = $result_set->result_array();
		
			return $result;
	}

	public function available_vehicles() {
		$today = date('Y-m-d');
		$this->db->select("vehicle.VEHICLE_ID, OWNER_ID, make, model, year_made, chassis_number, motor_number, color, type,
						plate_code, plate_number, libre_no, fuiel_type, cc, total_passanger, cylinder_count" );
		$this->db->from('vehicle');
		$this->db->join('rent', 'rent.VEHICLE_ID = vehicle.VEHICLE_ID', "left");

		$today = date('Y-m-d hh:mm:ss');
		$where = array('return_date <' => $today);	
		$or_where = array('return_date = ' => NULL);
		$this->db->where($where);
		$this->db->or_where($or_where);
		$query = $this->db->get();
		return $query->result_array();
	}


	public function add_vehicle($vehicle) {

			$data_model = $this->set_vehicle_data_model($vehicle);
			$data_model['OWNER_ID'] = $vehicle['OWNER_ID'];
			$result = $this->db->insert('vehicle', $data_model);
			
				if($this->db->affected_rows() == 1 ) {
					 $data_model['VEHICLE_ID'] =  $this->db->insert_id();
					 return $data_model;
		 		} else {
					return false;
				 }
	}

	public function update_vehicle($vehicle, $id) {
		$data_model = $this->set_vehicle_data_model($vehicle);

			$this->db->where('VEHICLE_ID', $id );
		
			$result = $this->db->update('vehicle', $data_model);
		if($this->db->affected_rows() == 1 ) {
				$data_model['VEHICLE_ID'] =  $id;
			return $data_model;
		 } else {
			return false;
		 }
	}

	public function delete_vehicle($id) {
		$deletedIds = [];
		$result = NULL;
		try {
		foreach ($id as $key => $value) {
				$deletedIds[] = $value;		
		}
			$this->db->where_in('VEHICLE_ID', $deletedIds);
			$result = $this->db->delete('vehicle');
		
			return ($this->db->affected_rows() > 0) ? true : false;
		
		} catch(Exception $e) {
			return false;
		}
		
	}

	private function set_vehicle_data_model($vehicle) {
		return array(

			'make' => $vehicle['make'],
			'model' => $vehicle['model'],
			'year_made' => $vehicle['year_made'],
			'color' => $vehicle['color'],
			'type' => $vehicle['type'],
			'chassis_number' => $vehicle['chassis_number'],
			'motor_number' => $vehicle['motor_number'],
			'fuiel_type' => $vehicle['fuiel_type'],
			'cc' => $vehicle['cc'],
			'total_passanger' => $vehicle['total_passanger'],
			'cylinder_count' => $vehicle['cylinder_count'],
			'plate_code' => $vehicle['plate_code'],
			'plate_number' => $vehicle['plate_number'],
			'libre_no' => $vehicle['libre_no']
		);
	}
}



?>
