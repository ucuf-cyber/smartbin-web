<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class History extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
        $this->load->model('Sensor_model');
    }

    public function index() {
        $start = $this->input->get('start_date');
        $end   = $this->input->get('end_date');
        $type  = $this->input->get('waste_type');

        $data['logs']        = $this->Sensor_model->get_history($start, $end, $type);
        $data['filter']      = ['start' => $start, 'end' => $end, 'type' => $type];
        $data['active_menu'] = 'history';

        $this->load->view('template/header', $data);
        $this->load->view('template/sidebar', $data);
        $this->load->view('history/index', $data);
        $this->load->view('template/footer');
    }
}
