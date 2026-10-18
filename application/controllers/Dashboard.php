<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
        $this->load->model('Dashboard_model');
        $this->load->model('Settings_model');
    }

    public function index() {
        $data['summary'] = $this->Dashboard_model->get_summary();
        $data['settings'] = $this->Settings_model->get_settings();
        $data['active_menu'] = 'dashboard';

        $this->load->view('template/header', $data);
        $this->load->view('template/sidebar', $data);
        $this->load->view('dashboard/index', $data);
        $this->load->view('template/footer');
    }
}
