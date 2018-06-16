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
		$this->form_validation->set_rules('first_name', 'First Name', 'required');
		$this->form_validation->set_rules('last_name', 'Last Name', 'required');
		$this->form_validation->set_rules('mobile_number', 'Mobile Number', 'required');
		$this->form_validation->set_rules('city', 'City', 'required');
		$this->form_validation->set_rules('sub_city', 'Sub-City', 'required');
		$this->form_validation->set_rules('wereda', 'Wereda', 'required');

		if($this->form_validation->run() === FALSE ) {
			$this->response(validation_errors(), API::HTTP_OK);
		} else {
			$result = '';
			if($id) {
			$result = $this->partner_model->update_partner($this->input->post(), $id);			
			} else {
				$result = $this->partner_model->add_partner($this->input->post());
			}
			$this->response($result, API::HTTP_OK);
		}

	}
}

?>
