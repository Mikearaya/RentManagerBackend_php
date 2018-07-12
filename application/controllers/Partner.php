<?php



class Partner extends API {

	public function __construct($config = 'rest') {
		parent::__construct($config);
		$this->load->model('partner_model');
	}

	public function index_get($id = NULL ) {
		
		$filter_string = $this->input->get('filter', TRUE);
		$sort = $this->input->get('sortOrder', TRUE);
		$page_size = $this->input->get('pageSize', TRUE);
		$page_number = $this->input->get('pageIndex', TRUE);
		$sort_column = $this->input->get('sortColumn', TRUE);

		$result = $this->partner_model->get_partner($id, $filter_string, $page_size, $page_number, $sort, $sort_column);
		$this->response($result, API::HTTP_OK);

	}

	public function index_post($id = NULL) {
		$this->load->library('form_validation');
		$this->set_validation_rules();
		if($this->form_validation->run() === FALSE ) {
			$this->response($this->validation_errors(), API::HTTP_BAD_REQUEST);
		} else {
			$result = '';
			if($id) {
			$result = $this->partner_model->update_partner($this->input->post(), $id);			
			} else {
				$result = $this->partner_model->add_partner($this->input->post());
			}


			if($result) {
				$this->response($result, API::HTTP_CREATED);
			} else {
				$this->response("Error occured while saving Partners Information!", API::HTTP_BAD_REQUEST);
			}
		}

	}

	public function delete_POST() {
		if($this->input->post('id')) {
			
			$result = $this->partner_model->delete_partner($this->input->post('id'));
			if($result) {
				$this->response($result, API::HTTP_OK);
			} else {
				$this->response($result, API::HTTP_BAD_REQUEST);
			}

		} else {
			$this->response("No Id Provided for Delete", API::HTTP_BAD_REQUEST);
		}
	}

	
	private function set_validation_rules() {
		$this->form_validation->set_rules('first_name', 'First Name', 'required');
		$this->form_validation->set_rules('last_name', 'Last Name', 'required');
		$this->form_validation->set_rules('mobile_number', 'Mobile Number', 
											'trim|required|numeric|min_length[10]|max_length[12]',
											array(
												'required' => '%s is Required',
												'numeric' => '%s should contain numbers only',
												'min_length' => '%s is short Valid number Should be 10 or 12 digits',
												'max_length' => '%s long Valid number Should be 10 or 12 digits',
												//'is_unique' => 'Provided %s Already exsists'
											)
																								);
		$this->form_validation->set_rules('city', 'City', 'required');
		$this->form_validation->set_rules('sub_city', 'Sub-City', 'required');
		$this->form_validation->set_rules('wereda', 'Wereda', 'required');
		$this->form_validation->set_rules('house_number', 'House Number', 'required');

	}
}

?>
