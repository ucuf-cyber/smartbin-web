<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends CI_Model {

    public function get_summary() {
        $this->load->model('Sensor_model');
        $latest = $this->Sensor_model->get_latest();

        return [
            'status'             => $latest ? $latest->status : 'Offline',
            'capacity_logam'     => $latest ? $latest->capacity_logam : 0,
            'capacity_non_logam' => $latest ? $latest->capacity_non_logam : 0,
            'waste_type'  => $latest ? $latest->waste_type : '-',
            'lid'         => $latest ? $latest->lid : 'Closed',
            'last_update' => $latest ? $latest->created_at : null,
            'logam_today'     => $this->Sensor_model->count_by_type('Logam'),
            'non_logam_today' => $this->Sensor_model->count_by_type('Non-Logam'),
            'trend'       => array_reverse($this->Sensor_model->get_recent_readings(20)),
            'activity'    => $this->Sensor_model->get_daily_activity(7),
        ];
    }
}
