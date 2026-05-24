<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\MahasiswaModel;
use App\Models\JurusanModel;
use App\Models\UserModel;

class Mahasiswa extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $db = \Config\Database::connect();

        $data = $db->table('mahasiswa')
            ->select('mahasiswa.id_mahasiswa, mahasiswa.npm, mahasiswa.nama')
            ->select('mahasiswa.kelas, mahasiswa.semester, mahasiswa.jenis_kelamin')
            ->select('jurusan.nama_jurusan, jurusan.kode_jurusan')
            ->select('users.username, users.role')
            ->join('jurusan', 'jurusan.id_jurusan = mahasiswa.id_jurusan', 'left')
            ->join('users', 'users.id_user = mahasiswa.id_user', 'left')
            ->get()
            ->getResultArray();

        return $this->respond($data);
    }

    public function show($id = null)
    {
        $db = \Config\Database::connect();

        $data = $db->table('mahasiswa')
            ->select('mahasiswa.id_mahasiswa, mahasiswa.npm, mahasiswa.nama')
            ->select('mahasiswa.kelas, mahasiswa.semester, mahasiswa.jenis_kelamin')
            ->select('jurusan.nama_jurusan, jurusan.kode_jurusan')
            ->select('users.username, users.role')
            ->join('jurusan', 'jurusan.id_jurusan = mahasiswa.id_jurusan', 'left')
            ->join('users', 'users.id_user = mahasiswa.id_user', 'left')
            ->where('mahasiswa.id_mahasiswa', $id)
            ->get()
            ->getRowArray();

        if (!$data) {
            return $this->failNotFound('Data mahasiswa tidak ditemukan');
        }

        return $this->respond($data);
    }

    public function create()
    {
        $model = new MahasiswaModel();
        $input = $this->request->getJSON(true);

        if (!$input) {
            $input = $this->request->getPost();
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'npm'          => 'required|max_length[20]|is_unique[mahasiswa.npm]',
            'nama'         => 'required|min_length[3]|max_length[100]',
            'kelas'        => 'required|max_length[10]',
            'semester'     => 'required|integer',
            'jenis_kelamin'=> 'required|in_list[L,P]',
            'id_jurusan'   => 'required|integer',
            'id_user'      => 'required|integer',
        ], [
            'npm' => [
                'required'   => 'NPM wajib diisi.',
                'max_length' => 'NPM maksimal 20 karakter.',
                'is_unique'  => 'NPM sudah terdaftar.',
            ],
            'nama' => [
                'required'   => 'Nama wajib diisi.',
                'min_length' => 'Nama minimal 3 karakter.',
                'max_length' => 'Nama maksimal 100 karakter.',
            ],
            'kelas' => [
                'required'   => 'Kelas wajib diisi.',
                'max_length' => 'Kelas maksimal 10 karakter.',
            ],
            'semester' => [
                'required' => 'Semester wajib diisi.',
                'integer'  => 'Semester harus berupa angka.',
            ],
            'jenis_kelamin' => [
                'required' => 'Jenis kelamin wajib diisi.',
                'in_list'  => 'Jenis kelamin harus L atau P.',
            ],
            'id_jurusan' => [
                'required' => 'Jurusan wajib dipilih.',
                'integer'  => 'ID jurusan harus berupa angka.',
            ],
            'id_user' => [
                'required' => 'User wajib dipilih.',
                'integer'  => 'ID user harus berupa angka.',
            ],
        ]);

        if (!$validation->run($input)) {
            return $this->failValidationErrors($validation->getErrors());
        }

        // Cek apakah id_jurusan ada
        $jurusanModel = new JurusanModel();
        if (!$jurusanModel->find($input['id_jurusan'])) {
            return $this->failValidationErrors('Jurusan tidak ditemukan.');
        }

        // Cek apakah id_user ada
        $userModel = new UserModel();
        if (!$userModel->find($input['id_user'])) {
            return $this->failValidationErrors('User tidak ditemukan.');
        }

        $data = [
            'npm'           => $input['npm'],
            'nama'          => $input['nama'],
            'kelas'         => $input['kelas'],
            'semester'      => $input['semester'],
            'jenis_kelamin' => $input['jenis_kelamin'],
            'id_jurusan'    => $input['id_jurusan'],
            'id_user'       => $input['id_user'],
        ];

        $model->insert($data);
        $id = $model->getInsertID();

        return $this->respondCreated([
            'status'  => 201,
            'message' => 'Data mahasiswa berhasil ditambahkan',
            'data'    => $model->find($id),
        ]);
    }

    public function update($id = null)
    {
        $model     = new MahasiswaModel();
        $mahasiswa = $model->find($id);

        if (!$mahasiswa) {
            return $this->failNotFound('Data mahasiswa tidak ditemukan');
        }

        $input = $this->request->getJSON(true);

        if (!$input) {
            $input = $this->request->getRawInput();
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'npm'           => "required|max_length[20]|is_unique[mahasiswa.npm,id_mahasiswa,{$id}]",
            'nama'          => 'required|min_length[3]|max_length[100]',
            'kelas'         => 'required|max_length[10]',
            'semester'      => 'required|integer',
            'jenis_kelamin' => 'required|in_list[L,P]',
            'id_jurusan'    => 'required|integer',
            'id_user'       => 'required|integer',
        ], [
            'npm' => [
                'required'   => 'NPM wajib diisi.',
                'max_length' => 'NPM maksimal 20 karakter.',
                'is_unique'  => 'NPM sudah terdaftar.',
            ],
            'nama' => [
                'required'   => 'Nama wajib diisi.',
                'min_length' => 'Nama minimal 3 karakter.',
                'max_length' => 'Nama maksimal 100 karakter.',
            ],
            'kelas' => [
                'required'   => 'Kelas wajib diisi.',
                'max_length' => 'Kelas maksimal 10 karakter.',
            ],
            'semester' => [
                'required' => 'Semester wajib diisi.',
                'integer'  => 'Semester harus berupa angka.',
            ],
            'jenis_kelamin' => [
                'required' => 'Jenis kelamin wajib diisi.',
                'in_list'  => 'Jenis kelamin harus L atau P.',
            ],
            'id_jurusan' => [
                'required' => 'Jurusan wajib dipilih.',
                'integer'  => 'ID jurusan harus berupa angka.',
            ],
            'id_user' => [
                'required' => 'User wajib dipilih.',
                'integer'  => 'ID user harus berupa angka.',
            ],
        ]);

        if (!$validation->run($input)) {
            return $this->failValidationErrors($validation->getErrors());
        }

        // Cek apakah id_jurusan ada
        $jurusanModel = new JurusanModel();
        if (!$jurusanModel->find($input['id_jurusan'])) {
            return $this->failValidationErrors('Jurusan tidak ditemukan.');
        }

        // Cek apakah id_user ada
        $userModel = new UserModel();
        if (!$userModel->find($input['id_user'])) {
            return $this->failValidationErrors('User tidak ditemukan.');
        }

        $model->update($id, [
            'npm'           => $input['npm'],
            'nama'          => $input['nama'],
            'kelas'         => $input['kelas'],
            'semester'      => $input['semester'],
            'jenis_kelamin' => $input['jenis_kelamin'],
            'id_jurusan'    => $input['id_jurusan'],
            'id_user'       => $input['id_user'],
        ]);

        return $this->respond([
            'status'  => 200,
            'message' => 'Data mahasiswa berhasil diperbarui',
            'data'    => $model->find($id),
        ]);
    }

    public function delete($id = null)
    {
        $model     = new MahasiswaModel();
        $mahasiswa = $model->find($id);

        if (!$mahasiswa) {
            return $this->failNotFound('Data mahasiswa tidak ditemukan');
        }

        $model->delete($id);

        return $this->respondDeleted([
            'status'  => 200,
            'message' => 'Data mahasiswa berhasil dihapus',
        ]);
    }
}