<?php
class Rent extends API {


	function __construct($config = 'rest') {
			parent::__construct($config);
			$this->load->model('rent_model');
	}

	public function index_get($rent_id = NULL) {
		$result = $this->rent_model->get_rent($rent_id);
		$this->response($result, API::HTTP_OK);
	}

	public function index_POST($resnt_id = NULL) {
		$this->load->library('form_validation');
		$this->form_validation->set_rules('initial_payment', 'Initial Payment', 'required');
		$this->form_validation->set_rules('owner_renting_price', 'Owners Renting Price', 'required');
		$this->form_validation->set_rules('rented_price', 'Renting Price', 'required');
		$this->form_validation->set_rules('start_date', 'Rent Start Date', 'required');
		$this->form_validation->set_rules('return_date', 'Rent Ending Date', 'required');
		$this->form_validation->set_rules('VEHICLE_ID', 'Rented Vehicle', 'required');
		$this->form_validation->set_rules('customer[first_name]', 'First Name', 'required');
		$this->form_validation->set_rules('customer[last_name]', 'Last Name', 'required');
		$this->form_validation->set_rules('customer[nationality]', 'Nationality', 'required');
		$this->form_validation->set_rules('customer[city]', 'Rent Ending Date', 'required');
		$this->form_validation->set_rules('customer[country]', 'Country', 'required');
		$this->form_validation->set_rules('customer[house_no]', 'House Number', 'required');
		$this->form_validation->set_rules('customer[mobile_number]', 'Mobile Number', 'required');
		$this->form_validation->set_rules('customer[id_type]', 'Rent Ending Date', 'required');
		$this->form_validation->set_rules('customer[id_number]', 'Rent Ending Date', 'required');


		if($this->form_validation->run() === FALSE ) {
			$this->response(validation_errors(), API::HTTP_OK);
		} else {
			$result = $this->rent_model->insert_rent($this->input->post());
			$this->response($result, API::HTTP_OK);
		}

	}

}

?>
