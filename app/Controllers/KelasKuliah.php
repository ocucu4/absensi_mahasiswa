<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\KelasKuliahModel;
use App\Models\MahasiswaModel;
use App\Models\JadwalModel;

class KelasKuliah extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $db = \Config\Database::connect();

        $data = $db->table('kelas_kuliah')
            ->select('kelas_kuliah.id_kelas_kuliah')
            ->select('mahasiswa.npm, mahasiswa.nama, mahasiswa.kelas')
            ->select('jadwal.hari, jadwal.jam_mulai, jadwal.jam_selesai, jadwal.ruangan')
            ->select('mata_kuliah.nama_matkul, mata_kuliah.kode_matkul')
            ->select('dosen.nama_dosen')
            ->join('mahasiswa', 'mahasiswa.id_mahasiswa = kelas_kuliah.id_mahasiswa', 'left')
            ->join('jadwal', 'jadwal.id_jadwal = kelas_kuliah.id_jadwal', 'left')
            ->join('mata_kuliah', 'mata_kuliah.id_matkul = jadwal.id_matkul', 'left')
            ->join('dosen', 'dosen.id_dosen = jadwal.id_dosen', 'left')
            ->get()
            ->getResultArray();

        return $this->respond($data);
    }

    public function show($id = null)
    {
        $db = \Config\Database::connect();

        $data = $db->table('kelas_kuliah')
            ->select('kelas_kuliah.id_kelas_kuliah')
            ->select('mahasiswa.npm, mahasiswa.nama, mahasiswa.kelas')
            ->select('jadwal.hari, jadwal.jam_mulai, jadwal.jam_selesai, jadwal.ruangan')
            ->select('mata_kuliah.nama_matkul, mata_kuliah.kode_matkul')
            ->select('dosen.nama_dosen')
            ->join('mahasiswa', 'mahasiswa.id_mahasiswa = kelas_kuliah.id_mahasiswa', 'left')
            ->join('jadwal', 'jadwal.id_jadwal = kelas_kuliah.id_jadwal', 'left')
            ->join('mata_kuliah', 'mata_kuliah.id_matkul = jadwal.id_matkul', 'left')
            ->join('dosen', 'dosen.id_dosen = jadwal.id_dosen', 'left')
            ->where('kelas_kuliah.id_kelas_kuliah', $id)
            ->get()
            ->getRowArray();

        if (!$data) {
            return $this->failNotFound('Data kelas kuliah tidak ditemukan');
        }

        return $this->respond($data);
    }

    public function create()
    {
        $model = new KelasKuliahModel();
        $input = $this->request->getJSON(true);

        if (!$input) {
            $input = $this->request->getPost();
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'id_mahasiswa' => 'required|integer',
            'id_jadwal'    => 'required|integer',
        ], [
            'id_mahasiswa' => [
                'required' => 'Mahasiswa wajib dipilih.',
                'integer'  => 'ID mahasiswa harus berupa angka.',
            ],
            'id_jadwal' => [
                'required' => 'Jadwal wajib dipilih.',
                'integer'  => 'ID jadwal harus berupa angka.',
            ],
        ]);

        if (!$validation->run($input)) {
            return $this->failValidationErrors($validation->getErrors());
        }

        // Cek apakah id_mahasiswa ada
        $mahasiswaModel = new MahasiswaModel();
        if (!$mahasiswaModel->find($input['id_mahasiswa'])) {
            return $this->failValidationErrors('Mahasiswa tidak ditemukan.');
        }

        // Cek apakah id_jadwal ada
        $jadwalModel = new JadwalModel();
        if (!$jadwalModel->find($input['id_jadwal'])) {
            return $this->failValidationErrors('Jadwal tidak ditemukan.');
        }

        // Cek apakah mahasiswa sudah terdaftar di jadwal yang sama
        $existing = $model->where('id_mahasiswa', $input['id_mahasiswa'])
                          ->where('id_jadwal', $input['id_jadwal'])
                          ->first();

        if ($existing) {
            return $this->failResourceExists('Mahasiswa sudah terdaftar di jadwal ini.');
        }

        $data = [
            'id_mahasiswa' => $input['id_mahasiswa'],
            'id_jadwal'    => $input['id_jadwal'],
        ];

        $model->insert($data);
        $id = $model->getInsertID();

        return $this->respondCreated([
            'status'  => 201,
            'message' => 'Mahasiswa berhasil didaftarkan ke kelas',
            'data'    => $model->find($id),
        ]);
    }

    public function delete($id = null)
    {
        $model       = new KelasKuliahModel();
        $kelasKuliah = $model->find($id);

        if (!$kelasKuliah) {
            return $this->failNotFound('Data kelas kuliah tidak ditemukan');
        }

        $model->delete($id);

        return $this->respondDeleted([
            'status'  => 200,
            'message' => 'Mahasiswa berhasil dikeluarkan dari kelas',
        ]);
    }
}