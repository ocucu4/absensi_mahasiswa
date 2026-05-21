<?php

namespace App\Models;

use CodeIgniter\Model;

class MahasiswaModel extends Model
{
    protected $table      = 'mahasiswa';
    protected $primaryKey = 'id_mahasiswa';

    protected $allowedFields = [
        
        'npm',
        'nama',
        'kelas',
        'semester',
        'jenis_kelamin',
        'id_jurusan',
        'id_user',

    ];

    protected $returnType = 'array';
}
