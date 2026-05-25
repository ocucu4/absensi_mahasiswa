<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;
use App\Models\DosenModel;
use App\Models\MahasiswaModel;

class Auth extends ResourceController
{
    protected $format = 'json';

    public function register()
    {
        $model = new UserModel();
        $input = $this->request->getJSON(true);

        if (!$input) {
            $input = $this->request->getPost();
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'username' => 'required|min_length[3]|max_length[100]|is_unique[users.username]',
            'password' => 'required|min_length[6]',
            'role'     => 'required|in_list[admin,dosen,mahasiswa]',
        ], [
            'username' => [
                'required'   => 'Username wajib diisi.',
                'min_length' => 'Username minimal 3 karakter.',
                'max_length' => 'Username maksimal 100 karakter.',
                'is_unique'  => 'Username sudah terdaftar.',
            ],
            'password' => [
                'required'   => 'Password wajib diisi.',
                'min_length' => 'Password minimal 6 karakter.',
            ],
            'role' => [
                'required' => 'Role wajib diisi.',
                'in_list'  => 'Role harus salah satu dari: admin, dosen, mahasiswa.',
            ],
        ]);

        if (!$validation->run($input)) {
            return $this->failValidationErrors($validation->getErrors());
        }

        // Kalau role dosen → wajib input NIDN dan cek di tabel dosen
        if ($input['role'] === 'dosen') {
            if (empty($input['nidn'])) {
                return $this->failValidationErrors('NIDN wajib diisi untuk role dosen.');
            }

            $dosenModel = new DosenModel();
            $dosen = $dosenModel->where('nidn', $input['nidn'])->first();

            if (!$dosen) {
                return $this->failValidationErrors('NIDN tidak ditemukan. Hubungi admin untuk mendaftarkan data dosen.');
            }

            if ($dosen['id_user'] !== null) {
                return $this->failValidationErrors('NIDN ini sudah memiliki akun.');
            }

            // Buat akun user
            $data = [
                'username' => $input['username'],
                'password' => password_hash($input['password'], PASSWORD_DEFAULT),
                'role'     => $input['role'],
            ];

            $model->insert($data);
            $id_user = $model->getInsertID();

            // Update id_user di tabel dosen
            $dosenModel->update($dosen['id_dosen'], [
                'id_user' => $id_user,
            ]);

            return $this->respondCreated([
                'status'  => 201,
                'message' => 'Registrasi dosen berhasil',
                'data'    => [
                    'id_user'  => $id_user,
                    'username' => $input['username'],
                    'role'     => $input['role'],
                    'nidn'     => $input['nidn'],
                ],
            ]);
        }

        // Kalau role mahasiswa → wajib input NPM dan cek di tabel mahasiswa
        if ($input['role'] === 'mahasiswa') {
            if (empty($input['npm'])) {
                return $this->failValidationErrors('NPM wajib diisi untuk role mahasiswa.');
            }

            $mahasiswaModel = new MahasiswaModel();
            $mahasiswa = $mahasiswaModel->where('npm', $input['npm'])->first();

            if (!$mahasiswa) {
                return $this->failValidationErrors('NPM tidak ditemukan. Hubungi admin untuk mendaftarkan data mahasiswa.');
            }

            if ($mahasiswa['id_user'] !== null) {
                return $this->failValidationErrors('NPM ini sudah memiliki akun.');
            }

            // Buat akun user
            $data = [
                'username' => $input['username'],
                'password' => password_hash($input['password'], PASSWORD_DEFAULT),
                'role'     => $input['role'],
            ];

            $model->insert($data);
            $id_user = $model->getInsertID();

            // Update id_user di tabel mahasiswa
            $mahasiswaModel->update($mahasiswa['id_mahasiswa'], [
                'id_user' => $id_user,
            ]);

            return $this->respondCreated([
                'status'  => 201,
                'message' => 'Registrasi mahasiswa berhasil',
                'data'    => [
                    'id_user'  => $id_user,
                    'username' => $input['username'],
                    'role'     => $input['role'],
                    'npm'      => $input['npm'],
                ],
            ]);
        }

        // Kalau role admin → langsung buat akun
        $data = [
            'username' => $input['username'],
            'password' => password_hash($input['password'], PASSWORD_DEFAULT),
            'role'     => $input['role'],
        ];

        $model->insert($data);
        $id = $model->getInsertID();

        return $this->respondCreated([
            'status'  => 201,
            'message' => 'Registrasi admin berhasil',
            'data'    => [
                'id_user'  => $id,
                'username' => $input['username'],
                'role'     => $input['role'],
            ],
        ]);
    }

    public function login()
    {
        $model = new UserModel();
        $input = $this->request->getJSON(true);

        if (!$input) {
            $input = $this->request->getPost();
        }

        if (empty($input['username']) || empty($input['password'])) {
            return $this->failValidationErrors('Username dan password wajib diisi.');
        }

        $user = $model->where('username', $input['username'])->first();

        if (!$user) {
            return $this->failUnauthorized('Username atau password salah.');
        }

        if (!password_verify($input['password'], $user['password'])) {
            return $this->failUnauthorized('Username atau password salah.');
        }

        $token = bin2hex(random_bytes(32));

        $model->update($user['id_user'], [
            'token'      => $token,
            'last_login' => date('Y-m-d H:i:s'),
        ]);

        return $this->respond([
            'status'  => 200,
            'message' => 'Login berhasil',
            'data'    => [
                'id_user'  => $user['id_user'],
                'username' => $user['username'],
                'role'     => $user['role'],
                'token'    => $token,
            ],
        ]);
    }

    public function logout()
    {
        $model      = new UserModel();
        $authHeader = $this->request->getHeaderLine('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->failUnauthorized('Token tidak ditemukan.');
        }

        $token = $matches[1];
        $user  = $model->where('token', $token)->first();

        if (!$user) {
            return $this->failUnauthorized('Token tidak valid.');
        }

        $model->update($user['id_user'], [
            'token' => null,
        ]);

        return $this->respond([
            'status'  => 200,
            'message' => 'Logout berhasil.',
        ]);
    }
}