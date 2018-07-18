<?php
class Rent extends API {


	function __construct($config = 'rest') {
			parent::__construct($config);
			$this->load->model('rent_model');
	}

	public function index_get($rent_id = NULL) {
		$filter_string = $this->input->get('filter', TRUE);
		$sort = $this->input->get('sortOrder', TRUE);
		$page_size = $this->input->get('pageSize', TRUE);
		$page_number = $this->input->get('pageIndex', TRUE);
		$sort_column = $this->input->get('sortColumn', TRUE);
		$result = $this->rent_model->get_rent($rent_id, $filter_string, $page_size, $page_number, $sort, $sort_column);
		$this->response($result, API::HTTP_OK);
	}

	public function contrat_info_get($rent_id) {
		$result = $this->rent_model->get_contrat_info($rent_id);
		$this->response($result, API::HTTP_OK);
	}

	public function extend_rent_POST($rentId) {
		$this->load->library('form_validation');
		$this->form_validation->set_rules('extended_days', 'Number of Extended Days', 'trim|required|numeric', 
															array(
																'required' => '%s is Reqired',
																'numeric' => '%s Should be only digits'
															));
		$this->form_validation->set_rules('initial_payment', 'Initial Payment', 'trim|required|numeric', 
		array(
			'required' => '%s is Reqired',
			'numeric' => '%s Should be only digits'
		));
		$this->form_validation->set_rules('owner_renting_price', 'Owner Renting Price', 'trim|required|numeric', 
		array(
			'required' => '%s is Reqired',
			'numeric' => '%s Should be only digits'
		));
		$this->form_validation->set_rules('rented_price', 'Actual Renting Price', 'trim|required|numeric', 
		array(
			'required' => '%s is Reqired',
			'numeric' => '%s Should be only digits'
		));
		if ($this->form_validation->run() === FALSE) {
			$this->response($this->validation_errors(), API::HTTP_BAD_REQUEST);
		} else {
			$result = $this->rent_model->extend_rent($this->input->post());

			if ($result ) {
				$this->response($result, API::HTTP_OK);
			} else {
				$this->response($result, API::HTTP_BAD_REQUEST);
			}
		}
		
	}


	public function index_POST() {
		$this->load->library('form_validation');
		
		self::set_rent_basics_validations();
		self::set_condition_validations();

		if($this->form_validation->run() === FALSE ) {
			$this->response($this->validation_errors(), API::HTTP_BAD_REQUEST);
		} else {
			$result = $this->rent_model->add_rent($this->input->post());
			$this->response($result, API::HTTP_OK);
		}

	}

	

	public function delete_POST($deletedRents) {
		if($this->input->post('id')) {
		$result = $this->rent_model->delete_rent($this->input->post('id'));
			if($result) {
				$this->response(['Rent Deleted Successfuly'], API::HTTP_OK);
			} else {
				$this->response(['error Deleting Rent'], API::HTTP_NOT_MODIFIED);
			}
		} else {
			$this->response(['Id fild not provided for delete'], API::HTTP_BAD_REQUEST);
		}
	}

	private function set_rent_basics_validations() {
	
		$this->form_validation->set_rules('initial_payment', 'Initial Payment', 'required');
		$this->form_validation->set_rules('owner_renting_price', 'Owners Renting Price', 'required');
		$this->form_validation->set_rules('rented_price', 'Renting Price', 'required');
		$this->form_validation->set_rules('start_date', 'Rent Start Date', 'required');
		$this->form_validation->set_rules('return_date', 'Rent Ending Date', 'required');
		$this->form_validation->set_rules('VEHICLE_ID', 'Rented Vehicle', 'required');

	}



	private function set_condition_validations() {

		$this->form_validation->set_rules('condition[window_controller]', 'Window Controller', 'required');
		$this->form_validation->set_rules('condition[seat_belt]', 'Seat Belt', 'required');
		$this->form_validation->set_rules('condition[spare_tire]', 'Spare Tire', 'required');
		$this->form_validation->set_rules('condition[wiper]', 'Wiper', 'required');
		$this->form_validation->set_rules('condition[crick_wrench]', 'Crick Wrench', 'required');
		$this->form_validation->set_rules('condition[dashboard_close]', 'Dashboard Close', 'required');
		$this->form_validation->set_rules('condition[mude_protecter]', 'Mude Protecter', 'required');
		$this->form_validation->set_rules('condition[spokio_outer]', 'Outside Spokios', 'required');
		$this->form_validation->set_rules('condition[spokio_inner]', 'Inside Mirror', 'required');
		$this->form_validation->set_rules('condition[sun_visor]', 'Sun Visor', 'required');
		$this->form_validation->set_rules('condition[mat_inner]', 'Inside Mats', 'required');
		$this->form_validation->set_rules('condition[wind_protecter]', 'Wind Protecter', 'required');
		$this->form_validation->set_rules('condition[blinker]', 'Blinker', 'required');
		$this->form_validation->set_rules('condition[radio]', 'Sterio', 'required');
		$this->form_validation->set_rules('condition[fuiel_level]', 'fuiel_level', 'required');
		$this->form_validation->set_rules('condition[cigaret_lighter]', 'Cigaret Lighter', 'required');
		$this->form_validation->set_rules('condition[fuiel_lid]', 'Fuiel Lid', 'required');
		$this->form_validation->set_rules('condition[radiator_lid]', 'Radiator Lid', 'required');
		$this->form_validation->set_rules('condition[crick]', 'Crick', 'required');

	}

}

?>
