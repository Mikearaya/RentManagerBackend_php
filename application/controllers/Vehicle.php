<?php


class Vehicle extends API {


		function __construct($config = 'rest') {
			parent::__construct();
			$this->load->model('vehicle_model');
		}
		public function index_get($id = NULL) {
			if(!$id){
			$result = $this->vehicle_model->get_vehicle($id);
			$this->response($result, API::HTTP_OK);
			} else {
				$this->filter_get();
			}

		}

		public function available_get() {
			$result = $this->vehicle_model->available_vehicles();
			$this->response($result, API::HTTP_OK);
		}

		public function filter_get() {
			$owner_id = $this->input->get('owner_id', TRUE);
			$filter_string = $this->input->get('filter', TRUE);
			$sort = $this->input->get('sortOrder', TRUE);
			$page_size = $this->input->get('pageSize', TRUE);
			$page_number = $this->input->get('pageNumber', TRUE);
			$sort_column = $this->input->get('sortColumn', TRUE);

			$result = $this->vehicle_model->filter_vehicle($owner_id, $filter_string, $page_number, $page_size, $sort, $sort_column);
			$this->response($result, API::HTTP_OK);
		}

		//handels updating and deleting of vehicles based on wether the id is given or not
		public function index_post($id = NULL) {
				$this->load->library('form_validation');

				$result = NULL;

				$this->form_validation->set_rules('OWNER_ID', 'Owner Id Number', 'required');
				$this->form_validation->set_rules('make', 'Car Make', 'required');
				$this->form_validation->set_rules('model', 'Car Model', 'required');
				$this->form_validation->set_rules('year_made', 'Year Made', 'required');
				$this->form_validation->set_rules('color', 'Color', 'required');
				$this->form_validation->set_rules('type', 'Car Type', 'required');
				$this->form_validation->set_rules('chassis_number', 'Chassis Number', 'required');
				$this->form_validation->set_rules('motor_number', 'Motor Number', 'required');
				$this->form_validation->set_rules('fuiel_type', 'Fuiel Type', 'required');
				$this->form_validation->set_rules('cc', 'Car CC', 'required');
				$this->form_validation->set_rules('total_passanger', 'Total Passanger', 'required');
				$this->form_validation->set_rules('cylinder_count', 'Number of Cylinders', 'required');
				$this->form_validation->set_rules('libre_no', 'Libre Number', 'required');
				$this->form_validation->set_rules('plate_code', 'Plate Code', 'required');
				$this->form_validation->set_rules('plate_number', 'Plate Number', 'required');

				if ($this->form_validation->run() === FALSE ) {
					$this->response(validation_errors(), API::HTTP_OK);
				} else {

					if(is_null($id)) {
						$result = $this->vehicle_model->add_vehicle($this->input->post());
					} else {
						$result = $this->vehicle_model->update_vehicle($this->input->post(), $id);
					}

					$this->response($result, API::HTTP_OK);
				}
				
		}
		
		//handels delete request for a single vehicle if id is passed on url or
		//multiple vehicles if the ids were passed as an array in http POST request
		public function index_delete($id = NULL) {
			if($id) {
				$result = $this->vehicle_model->delete_vehicle(array('VEHICLE_ID'=> $id));
			} else if($this->input->post()) {
				$result = $this->vehicle_model->delete_vehicle($this->input->post());
			} else {
				$result = false;
			}
			$this->response($result, API::HTTP_OK);
		}
}

?>
