<?php

namespace App\Models;

use CodeIgniter\Model;

class AbsensiModel extends Model
{
    protected $table      = 'absensi';
    protected $primaryKey = 'id_absensi';

    protected $allowedFields = [
        'pertemuan_ke',
        'tanggal',
        'status',
        'jam_absen',
        'id_kelas_kuliah',
    ];

    protected $returnType = 'array';
}
