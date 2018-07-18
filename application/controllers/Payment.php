<?php
class Payment extends API {

    function __construct($config = 'rest') {
        parent::__construct($config);
        $this->load->model('payment_model');
    }

    function index_GET($catagory = 'ALL') {
		$filter_string = $this->input->get('filter', TRUE);
		$sort = $this->input->get('sortOrder', TRUE);
		$page_size = $this->input->get('pageSize', TRUE);
		$page_number = $this->input->get('pageIndex', TRUE);
		$sort_column = $this->input->get('sortColumn', TRUE);
        $result = $this->payment_model->get_rent_payment($catagory, $filter_string, $sort_column, $sort, $page_number, $page_size);
        $this->response($result, API::HTTP_OK);
	}
	
	function index_POST() {
		$this->load->library('form_validation');

		$this->form_validation->set_rules('RENT_ID', 'Rent is Required', 'trim|required|numeric');
		$this->form_validation->set_rules('payment_amount', 'Payment Amount', 'trim|required|numeric', 
	                           array(
								   'required' => '%s is Required!',
								   'numeric' => '%s Should be numeric value'
							   ));
		if($this->form_validation->run() === FALSE) {
			$this->response($this->validation_errors(), API::HTTP_BAD_REQUEST);
		} else {
			$result = $this->payment_model->add_payment($this->input->post());
			if($result) {
				$this->response($result, API::HTTP_OK);
			} else {
				$this->response(["Something Went Wrong While saving payment, try again"], API::HTTP_BAD_REQUEST);
			}
		}
	}
}

?>
