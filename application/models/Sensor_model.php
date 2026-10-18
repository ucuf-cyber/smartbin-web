<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sensor_model extends CI_Model {

    private $table = 'sensor_logs';

    public function insert_log($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }

    public function get_latest() {
        return $this->db->order_by('id', 'DESC')
                         ->limit(1)
                         ->get($this->table)
                         ->row();
    }

    // Riwayat cuma menampilkan baris 'event' (barang benar-benar masuk & dipilah),
    // bukan baris 'heartbeat' (update kapasitas rutin tanpa aktivitas).
    public function get_history($start = null, $end = null, $type = null) {
        $this->db->from($this->table);
        $this->db->where('trigger_type', 'event');
        if ($start) $this->db->where('created_at >=', $start . ' 00:00:00');
        if ($end)   $this->db->where('created_at <=', $end . ' 23:59:59');
        if ($type)  $this->db->where('waste_type', $type);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function count_by_type($type) {
        return $this->db->where('waste_type', $type)
                         ->where('trigger_type', 'event')
                         ->where('DATE(created_at)', date('Y-m-d'))
                         ->count_all_results($this->table);
    }

    // Data untuk grafik tren kapasitas (N pembacaan terakhir)
    public function get_recent_readings($limit = 20) {
        return $this->db->order_by('id', 'DESC')
                         ->limit($limit)
                         ->get($this->table)
                         ->result_array();
    }

    // Rekap jumlah logam vs non-logam per hari (7 hari terakhir) untuk grafik aktivitas
    public function get_daily_activity($days = 7) {
        $this->db->select("DATE(created_at) as tanggal,
                            SUM(CASE WHEN waste_type = 'Logam' THEN 1 ELSE 0 END) as logam,
                            SUM(CASE WHEN waste_type = 'Non-Logam' THEN 1 ELSE 0 END) as non_logam", false);
        $this->db->from($this->table);
        $this->db->where('trigger_type', 'event');
        $this->db->where('created_at >=', date('Y-m-d', strtotime("-{$days} days")) . ' 00:00:00');
        $this->db->group_by('DATE(created_at)');
        $this->db->order_by('tanggal', 'ASC');
        return $this->db->get()->result();
    }
}
