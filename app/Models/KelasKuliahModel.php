<?php

namespace App\Models;

use CodeIgniter\Model;

class KelasKuliahModel extends Model
{
    protected $table      = 'kelas_kuliah';
    protected $primaryKey = 'id_kelas_kuliah';

    protected $allowedFields = [
        'id_mahasiswa',
        'id_jadwal',
    ];

    protected $returnType = 'array';
}
