<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\JurusanModel;

class Jurusan extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $model = new JurusanModel();
        return $this->respond($model->findAll());
    }

    public function show($id = null)
    {
        $model = new JurusanModel();
        $data  = $model->find($id);

        if (!$data) {
            return $this->failNotFound('Data jurusan tidak ditemukan');
        }

        return $this->respond($data);
    }

    public function create()
    {
        $model = new JurusanModel();
        $input = $this->request->getJSON(true);

        if (!$input) {
            $input = $this->request->getPost();
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'kode_jurusan' => 'required|max_length[20]|is_unique[jurusan.kode_jurusan]',
            'nama_jurusan' => 'required|min_length[3]|max_length[100]',
        ], [
            'kode_jurusan' => [
                'required'   => 'Kode jurusan wajib diisi.',
                'max_length' => 'Kode jurusan maksimal 20 karakter.',
                'is_unique'  => 'Kode jurusan sudah terdaftar.',
            ],
            'nama_jurusan' => [
                'required'   => 'Nama jurusan wajib diisi.',
                'min_length' => 'Nama jurusan minimal 3 karakter.',
                'max_length' => 'Nama jurusan maksimal 100 karakter.',
            ],
        ]);

        if (!$validation->run($input)) {
            return $this->failValidationErrors($validation->getErrors());
        }

        $data = [
            'kode_jurusan' => $input['kode_jurusan'],
            'nama_jurusan' => $input['nama_jurusan'],
        ];

        $model->insert($data);
        $id = $model->getInsertID();

        return $this->respondCreated([
            'status'  => 201,
            'message' => 'Data jurusan berhasil ditambahkan',
            'data'    => $model->find($id),
        ]);
    }

    public function update($id = null)
    {
        $model   = new JurusanModel();
        $jurusan = $model->find($id);

        if (!$jurusan) {
            return $this->failNotFound('Data jurusan tidak ditemukan');
        }

        $input = $this->request->getJSON(true);

        if (!$input) {
            $input = $this->request->getRawInput();
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'kode_jurusan' => "required|max_length[20]|is_unique[jurusan.kode_jurusan,id_jurusan,{$id}]",
            'nama_jurusan' => 'required|min_length[3]|max_length[100]',
        ], [
            'kode_jurusan' => [
                'required'   => 'Kode jurusan wajib diisi.',
                'max_length' => 'Kode jurusan maksimal 20 karakter.',
                'is_unique'  => 'Kode jurusan sudah terdaftar.',
            ],
            'nama_jurusan' => [
                'required'   => 'Nama jurusan wajib diisi.',
                'min_length' => 'Nama jurusan minimal 3 karakter.',
                'max_length' => 'Nama jurusan maksimal 100 karakter.',
            ],
        ]);

        if (!$validation->run($input)) {
            return $this->failValidationErrors($validation->getErrors());
        }

        $model->update($id, [
            'kode_jurusan' => $input['kode_jurusan'],
            'nama_jurusan' => $input['nama_jurusan'],
        ]);

        return $this->respond([
            'status'  => 200,
            'message' => 'Data jurusan berhasil diperbarui',
            'data'    => $model->find($id),
        ]);
    }

    public function delete($id = null)
    {
        $model   = new JurusanModel();
        $jurusan = $model->find($id);

        if (!$jurusan) {
            return $this->failNotFound('Data jurusan tidak ditemukan');
        }

        $model->delete($id);

        return $this->respondDeleted([
            'status'  => 200,
            'message' => 'Data jurusan berhasil dihapus',
        ]);
    }
}