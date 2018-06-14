<?php


class Vehicle_model extends CI_Model {

	function __construct($config = 'rest') {
		parent::__construct($config);
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


	public function add_vehicle($vehicle) {
			$data_model = $this->set_vehicle_data_model($vehicle);
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
		$ids = [];
		$result = NULL;

		foreach ($id as $key => $value) {
			$this->db->where('VEHICLE_ID', $value);
			$result = $this->db->delete('vehicle');
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

	private function set_vehicle_data_model($vehicle) {
		return array(
			'OWNER_ID' => $vehicle['OWNER_ID'],
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
