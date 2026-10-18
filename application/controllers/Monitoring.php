<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Monitoring extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
        $this->load->model('Sensor_model');
        $this->load->model('Settings_model');
    }

    public function index() {
        $data['latest']       = $this->Sensor_model->get_latest();
        $data['settings']     = $this->Settings_model->get_settings();
        $data['active_menu']  = 'monitoring';

        $this->load->view('template/header', $data);
        $this->load->view('template/sidebar', $data);
        $this->load->view('monitoring/index', $data);
        $this->load->view('template/footer');
    }
}
