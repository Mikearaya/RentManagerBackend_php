<?php
class Customer extends API {

	function __construct($config = 'rest') {
		parent::__construct($config);
		$this->load->model('customer_model');
	}

	public function index_GET($id = NULL) {
		$result;
		
		$filter_string = $this->input->get('filter');
		$sort_column = $this->input->get('sort_column');
		$sort_order = $this->input->get('sort_order');
		$page_number = $this->input->get('page_index');
		$page_size = $this->input->get('page_size');

		if($id) {
			$result = $this->customer_model->get_customer($id);
		} else {
			$result = $this->customer_model->filter_customers($filter_string, $sort_column, $sort_order, $page_number, $page_size);
		}

		($result) ? $this->response($result, API::HTTP_OK) : $this->$response($result, API::HTTP_BAD_REQUEST);
	}


	public function add_POST() {
		$this->load->library('form_validation');
		$this->validate_customer_data();
		$this->form_validation->set_rules('driving_licence_id', 'Driving License ID', 'trim|required|is_unique[customer.driving_licence_id]');
		$this->form_validation->set_rules('mobile_number', 'Main Mobile Number', 'trim|required|is_unique[customer.mobile_number]');
		if($this->form_validation->run() === FALSE) {
			$this->response($this->validation_errors(), API::HTTP_BAD_REQUEST);
		} else {
			$result = $this->customer_model->add_customer($this->input->post());
			($result) ? $this->response($result, API::HTTP_CREATED) : $this->$response(["Unknown Error Occured While Saving Client Information"], API::HTTP_BAD_REQUEST);
		}
	}

	public function update_POST($id) {
		$this->load->library('form_validation');
			$this->validate_customer_data();
			
			if($this->form_validation->run() === FALSE) {
				$this->response($this->validation_errors(), API::HTTP_BAD_REQUEST);
			} else {
				$result = $this->customer_model->update_customer( $id, $this->input->post());
				($result) ? $this->response($result, API::HTTP_CREATED) : $this->response(["Unknown Error Occured While Saving Client Information"], API::HTTP_BAD_REQUEST);
			}
	}

	public function delete_POST() {
		if($this->input->post('id')) {
			$result = $this->customer_model->delete_customers($this->input->post('id'));
			($result) ? $this->response($result, API::HTTP_OK) : $this->response($result, API::HTTP_BAD_REQUEST);
		} else {
			$this->response("ID Should Be Set", API::HTTP_BAD_REQUEST);
		}
	}
	public function index_DELETE($id) {
		if($id) {
			$result = $this->customer_model->delete_customer($id);
			($result) ? $this->response($result, API::HTTP_OK) : $this->response($result, API::HTTP_BAD_REQUEST);
		} else {
			$this->response("ID Should Be Set", API::HTTP_BAD_REQUEST);
		}
	}


	private function validate_customer_data() {		
		$this->form_validation->set_rules('first_name', 'Customer First Name', 'trim|required');
		$this->form_validation->set_rules('last_name', 'Customer Last Name', 'trim|required');
		
		$this->form_validation->set_rules('nationality', 'Nationality', 'trim|required');
		$this->form_validation->set_rules('country', 'Country', 'trim|required');
		$this->form_validation->set_rules('city', 'City', 'trim|required');
		$this->form_validation->set_rules('house_no', 'House Number', 'trim|required');

	}

}


?>
