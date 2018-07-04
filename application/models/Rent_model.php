<?php

class Rent_model extends CI_Model {

    function __construct() {
		parent::__construct();
		$this->load->database();
	}
	
	public function get_rent($id = NULL, $filter_string, $page_size, $page_number, $sort_order, $sort_column) {

		$result = [];
		$this->db->select("RENT_ID, CONCAT(first_name,'  ',last_name ) as rented_by, 
						CONCAT(plate_code,'-',plate_number) as plate_number,start_date, return_date,
						vehicle.make, vehicle.model, vehicle.type, vehicle.color, vehicle.fuiel_type,
						vehicle.cc, customer.mobile_number, customer.city, customer.driving_licence_id, customer.passport_number,
						customer.nationality, owner_renting_price, initial_payment, customer.hotel_name, customer.hotel_phone,
						added_on, rent.updated_on, rented_price");
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

			$this->db->limit($page_size , $start);
			$result_set = $this->db->get();
			$result['total'] = $this->db->count_all('rent');
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
		$customer = $this->set_customer_data_model($rent_detail['customer']);
		$rent = $this->set_rent_data_model($rent_detail);
		$condition = $this->set_condition_data_model($rent_detail['condition']);

		$this->db->insert('customer', $customer);
		$customer_id = $this->db->insert_id();
	
			if($customer_id) {
				$rent['CUSTOMER_ID'] = $customer_id;				
				$this->db->insert('rent', $rent);
				$rent_id = $this->db->insert_id();
					if ($rent_id) {
						$condition['RENT_ID'] = $rent_id;
						$this->db->insert('vehicle_condition',$condition );

						$result = ($this->db->affected_rows() > 0) ? $rent_id : false;
						
					} else {
						$result = false;
					}
			} else {
						$result =  false;
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
				'passport_number' => $customer['passport_number'],
				'driving_licence_id' => $customer['driving_licence_id'],
				'mobile_number' => $customer['mobile_number'],
				'nationality' => $customer['nationality'],
				'country' => $customer['country'],
				'city' => $customer['city'],
				'house_no' => $customer['house_no'],
				'hotel_name' => $customer['hotel_name'],
				'hotel_phone' => $customer['hotel_phone']
			);

				if(trim($customer['other_phone'])) {
					$customer_data_model['other_phone'] = $customer['other_phone'];
				}
		return $customer_data_model;
	}
	public function get_contrat_info($rent_id) {
		try {
				$this->db->select("r.RENT_ID, r.VEHICLE_ID, c.CUSTOMER_ID, r.start_date, r.return_date, DATEDIFF( r.return_date, r.start_date) as 'duration', r.initial_payment, r.added_on,
									r.colateral_deposit, r.rented_price, (DATEDIFF( r.return_date, r.start_date) * r.rented_price) as 'total_payment',
									((DATEDIFF( r.return_date, r.start_date) * r.rented_price) - r.initial_payment) as 'remaining_payment', v.make, v.model, v.year_made,
									v.color, v.type, v.chassis_number, v.motor_number, v.fuiel_type, v.cylinder_count, v.libre_no,  v.plate_code, v.plate_number,
									v.cc, v.total_passanger, c.first_name, c.last_name, c.passport_number, c.nationality, c.country, c.city, c.house_no,
									c.mobile_number, c.other_phone, c.hotel_name, c.nationality, c.driving_licence_id, c.hotel_phone, v_c.window_controller, v_c.seat_belt, 
									v_c.spare_tire, v_c.wiper, v_c.crick_wrench, v_c.dashboard_close, v_c.mude_protecter, v_c.spokio_inner, v_c.spokio_outer, v_c.sun_visor,
									v_c.wind_protecter, v_c.blinker, v_c.mat_inner, v_c.radio, v_c.fuiel_level, v_c.total_kilometer, v_c.crick, v_c.radiator_lid,v_c.fuiel_lid,
									v_c.cigaret_lighter, v_c.comment   ");
				$this->db->from('rent  r');
				$this->db->join('vehicle  v', 'r.VEHICLE_ID = v.VEHICLE_ID');
				$this->db->join('customer  c', 'r.CUSTOMER_ID = c.CUSTOMER_ID');
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
