<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings_model extends CI_Model {

    private $table = 'settings';

    public function get_settings() {
        $row = $this->db->limit(1)->get($this->table)->row();
        if (!$row) {
            // fallback default jika tabel settings kosong
            return (object) [
                'id' => 0, 'warning_limit' => 80, 'critical_limit' => 100,
                'delay_close' => 5, 'interval_send' => 5
            ];
        }
        return $row;
    }

    public function update_settings($data) {
        $current = $this->get_settings();
        if ($current->id == 0) {
            return $this->db->insert($this->table, $data);
        }
        return $this->db->where('id', $current->id)->update($this->table, $data);
    }
}
