<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

    // PENTING: pindahkan ke config tersendiri / environment variable sebelum produksi
    private $apiKey = "SMARTBIN_API_KEY_2026";

    public function __construct() {
        parent::__construct();
        $this->load->model('Sensor_model');
        $this->load->model('Settings_model');
        $this->load->helper('url');
    }

    private function json($payload, $status = 200) {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    // POST /api/send_data — dipanggil oleh firmware NodeMCU
    public function send_data() {
        $headers = $this->input->request_headers();
        $key = isset($headers['X-Api-Key']) ? $headers['X-Api-Key'] : '';

        if ($key !== $this->apiKey) {
            $this->json(['success' => false, 'message' => 'API Key tidak valid'], 401);
            return;
        }

        $data = json_decode($this->input->raw_input_stream, true);

        $allowedWaste   = ['Logam', 'Non-Logam', 'Unknown'];
        $allowedLid     = ['Open', 'Closed'];
        $allowedStatus  = ['Online', 'Offline'];
        $allowedTrigger = ['event', 'heartbeat'];

        $wasteType = $data['waste_type'] ?? 'Unknown';
        $lid       = $data['lid'] ?? 'Closed';
        $status    = $data['status'] ?? 'Online';
        $trigger   = $data['trigger_type'] ?? 'heartbeat';

        $insertData = [
            'capacity_logam'     => max(0, min(100, (int) ($data['capacity_logam'] ?? 0))),
            'capacity_non_logam' => max(0, min(100, (int) ($data['capacity_non_logam'] ?? 0))),
            'waste_type'         => in_array($wasteType, $allowedWaste) ? $wasteType : 'Unknown',
            'lid'                => in_array($lid, $allowedLid) ? $lid : 'Closed',
            'status'             => in_array($status, $allowedStatus) ? $status : 'Online',
            'trigger_type'       => in_array($trigger, $allowedTrigger) ? $trigger : 'heartbeat',
        ];

        $result = $this->Sensor_model->insert_log($insertData);

        $this->json([
            'success' => (bool) $result,
            'message' => $result ? 'Data berhasil disimpan' : 'Gagal menyimpan data'
        ]);
    }

    // GET /api/latest — dipoll oleh halaman Dashboard/Monitoring (AJAX, tanpa reload)
    public function latest() {
        if (!$this->session->userdata('logged_in')) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        $data = $this->Sensor_model->get_latest();
        $this->json(['success' => true, 'data' => $data]);
    }

    // GET /api/history?start_date=&end_date=&waste_type= — dipakai halaman Riwayat (filter AJAX opsional)
    public function history() {
        if (!$this->session->userdata('logged_in')) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        $start = $this->input->get('start_date');
        $end   = $this->input->get('end_date');
        $type  = $this->input->get('waste_type');

        $data = $this->Sensor_model->get_history($start, $end, $type);
        $this->json(['success' => true, 'data' => $data]);
    }

    // GET /api/summary — dipakai Dashboard untuk memperbarui kartu ringkasan & grafik tanpa reload
    public function summary() {
        if (!$this->session->userdata('logged_in')) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        $latest   = $this->Sensor_model->get_latest();
        $trend    = array_reverse($this->Sensor_model->get_recent_readings(20));
        $activity = $this->Sensor_model->get_daily_activity(7);
        $settings = $this->Settings_model->get_settings();

        $this->json([
            'success'  => true,
            'latest'   => $latest,
            'trend'    => $trend,
            'activity' => $activity,
            'settings' => $settings,
        ]);
    }
}
