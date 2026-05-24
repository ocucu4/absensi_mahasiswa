<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\MataKuliahModel;
use App\Models\JurusanModel;

class MataKuliah extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $db = \Config\Database::connect();

        $data = $db->table('mata_kuliah')
            ->select('mata_kuliah.id_matkul, mata_kuliah.kode_matkul, mata_kuliah.nama_matkul')
            ->select('mata_kuliah.sks, mata_kuliah.semester')
            ->select('jurusan.nama_jurusan, jurusan.kode_jurusan')
            ->join('jurusan', 'jurusan.id_jurusan = mata_kuliah.id_jurusan', 'left')
            ->get()
            ->getResultArray();

        return $this->respond($data);
    }

    public function show($id = null)
    {
        $db = \Config\Database::connect();

        $data = $db->table('mata_kuliah')
            ->select('mata_kuliah.id_matkul, mata_kuliah.kode_matkul, mata_kuliah.nama_matkul')
            ->select('mata_kuliah.sks, mata_kuliah.semester')
            ->select('jurusan.nama_jurusan, jurusan.kode_jurusan')
            ->join('jurusan', 'jurusan.id_jurusan = mata_kuliah.id_jurusan', 'left')
            ->where('mata_kuliah.id_matkul', $id)
            ->get()
            ->getRowArray();

        if (!$data) {
            return $this->failNotFound('Data mata kuliah tidak ditemukan');
        }

        return $this->respond($data);
    }

    public function create()
    {
        $model = new MataKuliahModel();
        $input = $this->request->getJSON(true);

        if (!$input) {
            $input = $this->request->getPost();
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'kode_matkul' => 'required|max_length[20]|is_unique[mata_kuliah.kode_matkul]',
            'nama_matkul' => 'required|min_length[3]|max_length[100]',
            'sks'         => 'required|integer|greater_than[0]',
            'semester'    => 'required|integer|greater_than[0]',
            'id_jurusan'  => 'required|integer',
        ], [
            'kode_matkul' => [
                'required'   => 'Kode mata kuliah wajib diisi.',
                'max_length' => 'Kode mata kuliah maksimal 20 karakter.',
                'is_unique'  => 'Kode mata kuliah sudah terdaftar.',
            ],
            'nama_matkul' => [
                'required'   => 'Nama mata kuliah wajib diisi.',
                'min_length' => 'Nama mata kuliah minimal 3 karakter.',
                'max_length' => 'Nama mata kuliah maksimal 100 karakter.',
            ],
            'sks' => [
                'required'     => 'SKS wajib diisi.',
                'integer'      => 'SKS harus berupa angka.',
                'greater_than' => 'SKS harus lebih dari 0.',
            ],
            'semester' => [
                'required'     => 'Semester wajib diisi.',
                'integer'      => 'Semester harus berupa angka.',
                'greater_than' => 'Semester harus lebih dari 0.',
            ],
            'id_jurusan' => [
                'required' => 'Jurusan wajib dipilih.',
                'integer'  => 'ID jurusan harus berupa angka.',
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

        $data = [
            'kode_matkul' => $input['kode_matkul'],
            'nama_matkul' => $input['nama_matkul'],
            'sks'         => $input['sks'],
            'semester'    => $input['semester'],
            'id_jurusan'  => $input['id_jurusan'],
        ];

        $model->insert($data);
        $id = $model->getInsertID();

        return $this->respondCreated([
            'status'  => 201,
            'message' => 'Data mata kuliah berhasil ditambahkan',
            'data'    => $model->find($id),
        ]);
    }

    public function update($id = null)
    {
        $model    = new MataKuliahModel();
        $matkul   = $model->find($id);

        if (!$matkul) {
            return $this->failNotFound('Data mata kuliah tidak ditemukan');
        }

        $input = $this->request->getJSON(true);

        if (!$input) {
            $input = $this->request->getRawInput();
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'kode_matkul' => "required|max_length[20]|is_unique[mata_kuliah.kode_matkul,id_matkul,{$id}]",
            'nama_matkul' => 'required|min_length[3]|max_length[100]',
            'sks'         => 'required|integer|greater_than[0]',
            'semester'    => 'required|integer|greater_than[0]',
            'id_jurusan'  => 'required|integer',
        ], [
            'kode_matkul' => [
                'required'   => 'Kode mata kuliah wajib diisi.',
                'max_length' => 'Kode mata kuliah maksimal 20 karakter.',
                'is_unique'  => 'Kode mata kuliah sudah terdaftar.',
            ],
            'nama_matkul' => [
                'required'   => 'Nama mata kuliah wajib diisi.',
                'min_length' => 'Nama mata kuliah minimal 3 karakter.',
                'max_length' => 'Nama mata kuliah maksimal 100 karakter.',
            ],
            'sks' => [
                'required'     => 'SKS wajib diisi.',
                'integer'      => 'SKS harus berupa angka.',
                'greater_than' => 'SKS harus lebih dari 0.',
            ],
            'semester' => [
                'required'     => 'Semester wajib diisi.',
                'integer'      => 'Semester harus berupa angka.',
                'greater_than' => 'Semester harus lebih dari 0.',
            ],
            'id_jurusan' => [
                'required' => 'Jurusan wajib dipilih.',
                'integer'  => 'ID jurusan harus berupa angka.',
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

        $model->update($id, [
            'kode_matkul' => $input['kode_matkul'],
            'nama_matkul' => $input['nama_matkul'],
            'sks'         => $input['sks'],
            'semester'    => $input['semester'],
            'id_jurusan'  => $input['id_jurusan'],
        ]);

        return $this->respond([
            'status'  => 200,
            'message' => 'Data mata kuliah berhasil diperbarui',
            'data'    => $model->find($id),
        ]);
    }

    public function delete($id = null)
    {
        $model  = new MataKuliahModel();
        $matkul = $model->find($id);

        if (!$matkul) {
            return $this->failNotFound('Data mata kuliah tidak ditemukan');
        }

        $model->delete($id);

        return $this->respondDeleted([
            'status'  => 200,
            'message' => 'Data mata kuliah berhasil dihapus',
        ]);
    }
}