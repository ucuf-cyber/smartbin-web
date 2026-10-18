<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Admin_model');
        $this->load->library('form_validation');
    }

    public function index() {
        // Jika sudah login, langsung ke dashboard
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }
        $this->load->view('login/index');
    }

    public function process() {
        $this->form_validation->set_rules('username', 'Username', 'required|trim');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', 'Username dan password wajib diisi.');
            redirect('login');
            return;
        }

        $username = $this->input->post('username', TRUE);
        $password = $this->input->post('password');

        $admin = $this->Admin_model->verify_login($username, $password);

        if ($admin) {
            $this->session->set_userdata([
                'logged_in' => TRUE,
                'admin_id'  => $admin->id,
                'username'  => $admin->username,
            ]);
            $this->Admin_model->log_activity($admin->id, 'Login ke sistem');
            redirect('dashboard');
        } else {
            $this->session->set_flashdata('error', 'Username atau password salah.');
            redirect('login');
        }
    }

    public function logout() {
        $adminId = $this->session->userdata('admin_id');
        if ($adminId) {
            $this->Admin_model->log_activity($adminId, 'Logout dari sistem');
        }
        $this->session->sess_destroy();
        redirect('login');
    }
}
