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

						$result = ($this->db->affected_rows() > 0) ? true : false;
						
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
				'house_no' => $customer['house_no']
			);

				if(trim($customer['other_phone'])) {
					$customer_data_model['other_phone'] = $customer['other_phone'];
				}
		return $customer_data_model;
	}

	private function set_condition_data_model($vehicle_condition) {
		$vehicle_condition_data_model = array(
			'window_controller' => $vehicle_condition['window_controller'],
			'seat_belt' => $vehicle_condition['seat_belt'],	
			'spare_tire' => $vehicle_condition['spare_tire'],	
			'wiper' => $vehicle_condition['wiper'],	
			'crick_wrench' => $vehicle_condition['crick_wrench'],	
			'dashboard_close' => $vehicle_condition['dashboard_close'],	
			'mude_protecter' => $vehicle_condition['mude_protecter'],	
			'spokio_inner' => $vehicle_condition['spokio_inner'],	
			'spokio_outer' => $vehicle_condition['spokio_outer'],	
			'spokio_inner' => $vehicle_condition['spokio_inner'],	
			'sun_visor' => $vehicle_condition['sun_visor'],	
			'mat_inner' => $vehicle_condition['mat_inner'],	
			'wind_protecter' => $vehicle_condition['wind_protecter'],	
			'blinker' => $vehicle_condition['blinker'],	
			'radio' => $vehicle_condition['radio'],	
			'fuiel_level' => $vehicle_condition['fuiel_level'],	
			'cigaret_lighter' => $vehicle_condition['cigaret_lighter'],	
			'fuiel_lid' => $vehicle_condition['fuiel_lid'],	
			'radiator_lid' => $vehicle_condition['radiator_lid'],	
			'crick' => $vehicle_condition['crick']
		);

				if(isset($vehicle_condition['comment']) && trim($vehicle_condition['comment']) ) {
					$vehicle_condition_data_model['comment'] = trim($vehicle_condition['comment']);
				}

		return $vehicle_condition_data_model;
	}


}

?>
