<?php

namespace App\Models;

use CodeIgniter\Model;

class MataKuliahModel extends Model
{
    protected $table      = 'mata_kuliah';
    protected $primaryKey = 'id_matkul';

    protected $allowedFields = [
        'kode_matkul',
        'nama_matkul',
        'sks',
        'semester',
        'id_jurusan',
    ];

    protected $returnType = 'array';
}
