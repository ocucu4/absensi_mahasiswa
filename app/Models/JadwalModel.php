<?php

namespace App\Models;

use CodeIgniter\Model;

class JadwalModel extends Model
{
    protected $table      = 'jadwal';
    protected $primaryKey = 'id_jadwal';

    protected $allowedFields = [
        'kelas',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'ruangan',
        'tahun_ajaran',
        'semester',
        'id_matkul',
        'id_dosen',
    ];

    protected $returnType = 'array';
}
