<?php

class Dashboard extends API {

	function __construct($config = 'rest') {
		parent::__construct($config);
		$this->load->model('dashboard_model');
	}

	function index_GET() {
		$result['monthRentCount'] = $this->dashboard_model->year_rent_count_by_month();
		$result['currentMonthRentCount'] = $this->dashboard_model->year_rent_count_by_month('current');
		$result['customers'] = $this->dashboard_model->number_of_customers();
		$result['vehicles'] = $this->dashboard_model->number_of_vehicles(); 
		$result['partners'] = $this->dashboard_model->number_of_partners(); 
		$result['payment'] = $this->dashboard_model->rent_payment_summary(); 
		$this->response($result, API::HTTP_OK);
	}
}

?>
