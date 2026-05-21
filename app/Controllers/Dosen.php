<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\DosenModel;

class Dosen extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $model = new DosenModel();
        return $this->respond($model->findAll());
    }

    public function show($id = null)
    {
        $model = new DosenModel();
        $data  = $model->find($id);

        if (!$data) {
            return $this->failNotFound('Data dosen tidak ditemukan');
        }

        return $this->respond($data);
    }

    public function create()
    {
        $model = new DosenModel();
        $input = $this->request->getJSON(true);

        if (!$input) {
            $input = $this->request->getPost();
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'nidn'       => 'required|max_length[20]',
            'nama_dosen' => 'required|min_length[3]|max_length[100]',
            'id_user'    => 'required|integer',
        ], [
            'nidn' => [
                'required'   => 'NIDN wajib diisi.',
                'max_length' => 'NIDN maksimal 20 karakter.',
            ],
            'nama_dosen' => [
                'required'   => 'Nama dosen wajib diisi.',
                'min_length' => 'Nama dosen minimal 3 karakter.',
                'max_length' => 'Nama dosen maksimal 100 karakter.',
            ],
            'id_user' => [
                'required' => 'ID user wajib diisi.',
                'integer'  => 'ID user harus berupa angka.',
            ],
        ]);

        if (!$validation->run($input)) {
            return $this->failValidationErrors($validation->getErrors());
        }

        $data = [
            'nidn'       => $input['nidn'],
            'nama_dosen' => $input['nama_dosen'],
            'id_user'    => $input['id_user'],
        ];

        $model->insert($data);
        $id = $model->getInsertID();

        return $this->respondCreated([
            'status'  => 201,
            'message' => 'Data dosen berhasil ditambahkan',
            'data'    => $model->find($id),
        ]);
    }

    public function update($id = null)
    {
        $model = new DosenModel();
        $dosen = $model->find($id);

        if (!$dosen) {
            return $this->failNotFound('Data dosen tidak ditemukan');
        }

        $input = $this->request->getJSON(true);

        if (!$input) {
            $input = $this->request->getRawInput();
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'nidn'       => 'required|max_length[20]',
            'nama_dosen' => 'required|min_length[3]|max_length[100]',
            'id_user'    => 'required|integer',
        ], [
            'nidn' => [
                'required'   => 'NIDN wajib diisi.',
                'max_length' => 'NIDN maksimal 20 karakter.',
            ],
            'nama_dosen' => [
                'required'   => 'Nama dosen wajib diisi.',
                'min_length' => 'Nama dosen minimal 3 karakter.',
                'max_length' => 'Nama dosen maksimal 100 karakter.',
            ],
            'id_user' => [
                'required' => 'ID user wajib diisi.',
                'integer'  => 'ID user harus berupa angka.',
            ],
        ]);

        if (!$validation->run($input)) {
            return $this->failValidationErrors($validation->getErrors());
        }

        $model->update($id, [
            'nidn'       => $input['nidn'],
            'nama_dosen' => $input['nama_dosen'],
            'id_user'    => $input['id_user'],
        ]);

        return $this->respond([
            'status'  => 200,
            'message' => 'Data dosen berhasil diperbarui',
            'data'    => $model->find($id),
        ]);
    }

    public function delete($id = null)
    {
        $model = new DosenModel();
        $dosen = $model->find($id);

        if (!$dosen) {
            return $this->failNotFound('Data dosen tidak ditemukan');
        }

        $model->delete($id);

        return $this->respondDeleted([
            'status'  => 200,
            'message' => 'Data dosen berhasil dihapus',
        ]);
    }
}