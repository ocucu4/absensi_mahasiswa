<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id_user';

    protected $allowedFields = [
        'username',
        'password',
        'role',
        'id_ref',
        'token',
        'last_login',
        'status',
    ];

    protected $returnType = 'array';
}
