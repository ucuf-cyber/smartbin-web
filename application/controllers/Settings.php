<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
        $this->load->model('Settings_model');
        $this->load->library('form_validation');
    }

    public function index() {
        $data['settings']    = $this->Settings_model->get_settings();
        $data['active_menu'] = 'settings';

        $this->load->view('template/header', $data);
        $this->load->view('template/sidebar', $data);
        $this->load->view('settings/index', $data);
        $this->load->view('template/footer');
    }

    public function save() {
        $this->form_validation->set_rules('warning_limit', 'Batas Warning', 'required|numeric|greater_than[0]|less_than_equal_to[100]');
        $this->form_validation->set_rules('critical_limit', 'Batas Kritis', 'required|numeric|greater_than[0]|less_than_equal_to[100]');
        $this->form_validation->set_rules('delay_close', 'Delay Tutup', 'required|numeric|greater_than[0]');
        $this->form_validation->set_rules('interval_send', 'Interval Kirim Data', 'required|numeric|greater_than[0]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('settings');
            return;
        }

        $data = [
            'warning_limit'  => (int) $this->input->post('warning_limit'),
            'critical_limit' => (int) $this->input->post('critical_limit'),
            'delay_close'    => (int) $this->input->post('delay_close'),
            'interval_send'  => (int) $this->input->post('interval_send'),
        ];

        $this->Settings_model->update_settings($data);
        $this->session->set_flashdata('success', 'Pengaturan berhasil disimpan.');
        redirect('settings');
    }
}
