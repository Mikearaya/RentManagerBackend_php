<?php  

class Employee extends API {

	function __construct($config = 'rest') {
		parent::__construct($config);
		$this->load->model('employee_model');
	}

	public function index_GET($id = NULL) {
		$result;
		
		if($id) {
			$result = $this->employee_model->get_employee($id);
		} else {
			$result = $this->employee_model->get_all_employees();
		}

		($result) ? $this->response($result, API::HTTP_OK) : $this->response($result, API::HTTP_NOT_FOUND);
	}

	public function filter_get() {
		$filter_string = $this->input->get('filter_string');
		$sort_column = $this->input->get('sort_column');
		$sort_order = $this->input->get('sort_order');
		$page_number = $this->input->get('page_index');
		$page_size = $this->input->get('page_size');
		$result = $this->employee_model->filter_employees($filter_string, $sort_column, $sort_order, $page_number, $page_size);
		($result) ? $this->response($result, API::HTTP_OK) : $this->response($result, API::HTTP_NOT_FOUND);
	}
	public function add_POST() {
		$this->load->library('form_validation');
		if($this->validate_employee_data()) {
			$result = $this->employee_model->add_employee($this->input->post());	
			if(is_array($result)) {
				$this->response($result, API::HTTP_CREATED);
			} else {
				$this->response(false, API::HTTP_BAD_REQUEST);
			}
		} else {
			$this->response(c, API::HTTP_BAD_REQUEST);
		}

	}

	public function update_POST($id) {
		$this->load->library('form_validation');
		if($this->validate_employee_data()) {
			$result = $this->employee_model->update_employee($id, $this->input->post());	
			if($result) {
				$this->response($result, API::HTTP_CREATED);
			} else {
				$this->response(false, API::HTTP_BAD_REQUEST);
			}
		} else {
			$this->response(error_array(), API::HTTP_BAD_REQUEST);
		}

	}

	public function index_DELETE($id) {
			$result = $this->employee_model->delete_employee($id);	
			if($result) {
				$this->response($result, API::HTTP_OK);
			} else {
				$this->response(false, API::HTTP_BAD_REQUEST);
			}

	}
	private function validate_employee_data() {

		$this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
		$this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
		$this->form_validation->set_rules('city', 'City', 'trim|required');
		$this->form_validation->set_rules('sub_city', 'Sub-City', 'trim|required');
		$this->form_validation->set_rules('wereda', 'Wereda', 'trim|required|numeric');
		$this->form_validation->set_rules('house_number', 'House Number', 'trim|required');
		$this->form_validation->set_rules('phone_number', 'Phone Number', 'trim|required|numeric|min_length[10]|max_length[12]');

		return $this->form_validation->run();
	}

}



?>
