<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_model extends CI_Model {

    private $table = 'admin';

    public function get_by_username($username) {
        return $this->db->where('username', $username)
                         ->get($this->table)
                         ->row();
    }

    public function verify_login($username, $password) {
        $admin = $this->get_by_username($username);
        if ($admin && password_verify($password, $admin->password)) {
            return $admin;
        }
        return false;
    }

    public function log_activity($admin_id, $activity) {
        return $this->db->insert('activity_log', [
            'admin_id'   => $admin_id,
            'activity'   => $activity,
            'ip_address' => $this->input->ip_address(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
