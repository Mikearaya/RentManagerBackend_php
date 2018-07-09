<?php

class Rent_model extends CI_Model {

    function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->helper('date');
		
	}
	
	public function get_rent($id = NULL, $filter_string = '', $page_size = 100, $page_number = 0, $sort_order = 'ASC', $sort_column = '') {

		$result = [];

		$this->db->select("RENT_ID, CONCAT(c.first_name,'  ',c.last_name ) as rented_by, 
						CONCAT(v.plate_code,'-',v.plate_number) as plate_number, r.start_date, r.return_date,
						v.make, v.model, v.type, v.color, v.fuiel_type,
						v.cc, c.mobile_number, c.city, c.driving_licence_id,
						DATEDIFF(return_date, start_date) as total_days, CONCAT(e.first_name,'  ',e.last_name ) as renting_staff, e.EMPLOYEE_ID,
						c.passport_number, c.nationality, r.owner_renting_price, r.initial_payment,
						c.hotel_name, c.hotel_phone, r.added_on, r.updated_on, r.rented_price, r.colateral_deposit");
		$this->db->from('rent r');
		$today = date('Y-m-d hh:mm:ss');
		$array = array('return_date >' => $today);	
	
		$this->db->join('vehicle v', 'r.VEHICLE_ID = v.VEHICLE_ID');
		$this->db->join('employee e', 'r.RENTED_BY = e.EMPLOYEE_ID');
		$this->db->join('customer c' , 'c.CUSTOMER_ID = r.CUSTOMER_ID');
		$this->db->where($array);
		$this->db->order_by($sort_column, $sort_order);
		if(is_null($id)) {
		$this->db->where("(CONCAT(c.first_name,' ',c.last_name) LIKE '%".$filter_string."%' OR v.plate_number
							 LIKE '%".$filter_string."%' )", NULL, FALSE);
			
			if($page_number === 0) {
				$start = 0;
				$end = $page_size;
			} else {
				$start = $page_number * $page_size;
				$end = $start + $page_size;
			}
			$cloned = clone $this->db;
		
			$this->db->limit($page_size , $start);
			$result_set = $this->db->get();
			$result['total'] = $cloned->count_all_results();
		
			$result['rents'] = $result_set->result_array();
			return $result;
		} else {
				$this->db->where('RENT_ID',$id );
				$result = $this->db->get();
			return $result->row_array();
		} 
	}
	public function add_rent($rent_detail) {
		$result = false;
		$rent = $this->set_rent_data_model($rent_detail);
		$condition = $this->set_condition_data_model($rent_detail['condition']);

				$this->db->insert('rent', $rent);
				$rent_id = $this->db->insert_id();
					if ($rent_id) {
						$condition['RENT_ID'] = $rent_id;
						$this->db->insert('vehicle_condition',$condition );

						$result = ($this->db->affected_rows() > 0) ? $rent_id : false;
						
					} else {
						$result = false;
					}
		
		return $result;
	}

	public function delete_rent($deletedRents) {
		$ids = [];
		foreach ($deletedRents as $key => $value) {
			if($key == 'RENT_ID') {
			$rentIds[] = array(	'RENT_ID' => $value	);
			}
		}
		$query = $this->db->delete('rent', $rentIds);
	}

	private function set_rent_data_model($rent) {
		$rent_data_model = array(
			'VEHICLE_ID' => $rent['VEHICLE_ID'],
			'CUSTOMER_ID' => $rent['CUSTOMER_ID'],
			'RENTED_BY' => $rent['RENTED_BY'],
			'start_date' => $rent['start_date'],
			'return_date' => $rent['return_date'],
			'owner_renting_price' => $rent['owner_renting_price'],
			'initial_payment' => $rent['initial_payment'],
			'owner_renting_price' => $rent['owner_renting_price'],
			'rented_price' => $rent['rented_price'],
			'colateral_deposit' => $rent['colateral_deposit']
		);
		return $rent_data_model;
	}

	public function get_contrat_info($rent_id) {
		try {
				$this->db->select("r.RENT_ID, r.VEHICLE_ID, c.CUSTOMER_ID, DATE(r.start_date) as start_date, time(r.start_date) as start_time , DATE(r.return_date) as return_date, TIME(r.return_date) as return_time,
									 DATEDIFF( r.return_date, r.start_date) as 'duration', r.initial_payment, r.added_on,
									r.colateral_deposit, r.rented_price, (DATEDIFF( r.return_date, r.start_date) * r.rented_price) as 'total_payment',
									((DATEDIFF( r.return_date, r.start_date) * r.rented_price) - r.initial_payment) as 'remaining_payment', v.make, v.model, v.year_made,
									v.color, v.type, v.chassis_number, v.motor_number, v.fuiel_type, v.cylinder_count, v.libre_no,  v.plate_code, v.plate_number,
									v.cc, v.total_passanger, c.first_name, c.last_name, c.passport_number, c.nationality, c.country, c.city, c.house_no,
									c.mobile_number, c.other_phone, c.hotel_name, c.nationality, c.driving_licence_id, c.hotel_phone, v_c.window_controller, v_c.seat_belt, 
									v_c.spare_tire, v_c.wiper, v_c.crick_wrench, v_c.dashboard_close, v_c.mude_protecter, v_c.spokio_inner, v_c.spokio_outer, v_c.sun_visor,
									v_c.wind_protecter, v_c.blinker, v_c.mat_inner, v_c.radio, v_c.fuiel_level, v_c.total_kilometer, v_c.crick, v_c.radiator_lid,v_c.fuiel_lid,
									v_c.cigaret_lighter, v_c.comment , CONCAT(e.first_name, ' ', e.last_name) as renting_employee  ");
				$this->db->from('rent  r');
				$this->db->join('vehicle  v', 'r.VEHICLE_ID = v.VEHICLE_ID');
				$this->db->join('customer  c', 'r.CUSTOMER_ID = c.CUSTOMER_ID');
				$this->db->join('employee  e', 'r.RENTED_BY = e.EMPLOYEE_ID');
				$this->db->join('vehicle_condition  v_c', 'r.RENT_ID = v_c.RENT_ID');
				$this->db->where('r.RENT_ID', $rent_id);
				$result = $this->db->get();
			return $result->row_array();
		} catch(Exception $e) {
			echo $e->getMessage();
		}
	}



	private function set_condition_data_model($vehicle_condition) {
		$vehicle_condition_data_model;
		foreach ($vehicle_condition as $key => $value) {
			$vehicle_condition_data_model[$key] = $value;	
		}		
				if(isset($vehicle_condition['comment']) && trim($vehicle_condition['comment']) ) {
					$vehicle_condition_data_model['comment'] = trim($vehicle_condition['comment']);
				}

		return $vehicle_condition_data_model;
	}


}

?>
