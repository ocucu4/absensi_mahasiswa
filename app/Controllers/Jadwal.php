<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\JadwalModel;
use App\Models\DosenModel;
use App\Models\MataKuliahModel;

class Jadwal extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $db = \Config\Database::connect();

        $data = $db->table('jadwal')
            ->select('jadwal.id_jadwal, jadwal.kelas, jadwal.hari')
            ->select('jadwal.jam_mulai, jadwal.jam_selesai, jadwal.ruangan')
            ->select('jadwal.tahun_ajaran, jadwal.semester')
            ->select('mata_kuliah.nama_matkul, mata_kuliah.kode_matkul')
            ->select('dosen.nama_dosen, dosen.nidn')
            ->join('mata_kuliah', 'mata_kuliah.id_matkul = jadwal.id_matkul', 'left')
            ->join('dosen', 'dosen.id_dosen = jadwal.id_dosen', 'left')
            ->get()
            ->getResultArray();

        return $this->respond($data);
    }

    // Jadwal per dosen
    public function jadwalDosen($id_dosen = null)
    {
        $db = \Config\Database::connect();
    
        $data = $db->table('jadwal')
            ->select('jadwal.id_jadwal, jadwal.kelas, jadwal.hari')
            ->select('jadwal.jam_mulai, jadwal.jam_selesai, jadwal.ruangan')
            ->select('jadwal.tahun_ajaran, jadwal.semester')
            ->select('mata_kuliah.nama_matkul, mata_kuliah.kode_matkul')
            ->select('dosen.nama_dosen, dosen.nidn')
            ->join('mata_kuliah', 'mata_kuliah.id_matkul = jadwal.id_matkul', 'left')
            ->join('dosen', 'dosen.id_dosen = jadwal.id_dosen', 'left')
            ->where('jadwal.id_dosen', $id_dosen)
            ->get()
            ->getResultArray();
    
        if (empty($data)) {
            return $this->failNotFound('Jadwal tidak ditemukan untuk dosen ini');
        }
    
        return $this->respond($data);
    }
    
    // Jadwal per mahasiswa
    public function jadwalMahasiswa($id_mahasiswa = null)
    {
        $db = \Config\Database::connect();
    
        $data = $db->table('kelas_kuliah')
            ->select('jadwal.id_jadwal, jadwal.kelas, jadwal.hari')
            ->select('jadwal.jam_mulai, jadwal.jam_selesai, jadwal.ruangan')
            ->select('jadwal.tahun_ajaran, jadwal.semester')
            ->select('mata_kuliah.nama_matkul, mata_kuliah.kode_matkul')
            ->select('dosen.nama_dosen, dosen.nidn')
            ->join('jadwal', 'jadwal.id_jadwal = kelas_kuliah.id_jadwal', 'left')
            ->join('mata_kuliah', 'mata_kuliah.id_matkul = jadwal.id_matkul', 'left')
            ->join('dosen', 'dosen.id_dosen = jadwal.id_dosen', 'left')
            ->where('kelas_kuliah.id_mahasiswa', $id_mahasiswa)
            ->get()
            ->getResultArray();
    
        if (empty($data)) {
            return $this->failNotFound('Jadwal tidak ditemukan untuk mahasiswa ini');
        }
    
        return $this->respond($data);
    }

    public function show($id = null)
    {
        $db = \Config\Database::connect();

        $data = $db->table('jadwal')
            ->select('jadwal.id_jadwal, jadwal.kelas, jadwal.hari')
            ->select('jadwal.jam_mulai, jadwal.jam_selesai, jadwal.ruangan')
            ->select('jadwal.tahun_ajaran, jadwal.semester')
            ->select('mata_kuliah.nama_matkul, mata_kuliah.kode_matkul')
            ->select('dosen.nama_dosen, dosen.nidn')
            ->join('mata_kuliah', 'mata_kuliah.id_matkul = jadwal.id_matkul', 'left')
            ->join('dosen', 'dosen.id_dosen = jadwal.id_dosen', 'left')
            ->where('jadwal.id_jadwal', $id)
            ->get()
            ->getRowArray();

        if (!$data) {
            return $this->failNotFound('Data jadwal tidak ditemukan');
        }

        return $this->respond($data);
    }

    public function create()
    {
        $model  = new JadwalModel();
        $input  = $this->request->getJSON(true);

        if (!$input) {
            $input = $this->request->getPost();
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'kelas'         => 'required|max_length[10]',
            'hari'          => 'required|in_list[Senin,Selasa,Rabu,Kamis,Jumat,Sabtu]',
            'jam_mulai'     => 'required',
            'jam_selesai'   => 'required',
            'ruangan'       => 'required|max_length[50]',
            'tahun_ajaran'  => 'required|max_length[20]',
            'semester'      => 'required|integer',
            'id_matkul'     => 'required|integer',
            'id_dosen'      => 'required|integer',
        ], [
            'kelas'       => ['required' => 'Kelas wajib diisi.'],
            'hari'        => ['required' => 'Hari wajib diisi.', 'in_list' => 'Hari tidak valid.'],
            'jam_mulai'   => ['required' => 'Jam mulai wajib diisi.'],
            'jam_selesai' => ['required' => 'Jam selesai wajib diisi.'],
            'ruangan'     => ['required' => 'Ruangan wajib diisi.'],
            'tahun_ajaran'=> ['required' => 'Tahun ajaran wajib diisi.'],
            'semester'    => ['required' => 'Semester wajib diisi.', 'integer' => 'Semester harus berupa angka.'],
            'id_matkul'   => ['required' => 'Mata kuliah wajib dipilih.', 'integer' => 'ID matkul harus berupa angka.'],
            'id_dosen'    => ['required' => 'Dosen wajib dipilih.', 'integer' => 'ID dosen harus berupa angka.'],
        ]);

        if (!$validation->run($input)) {
            return $this->failValidationErrors($validation->getErrors());
        }

        // Cek apakah id_matkul ada
        $matkulModel = new MataKuliahModel();
        if (!$matkulModel->find($input['id_matkul'])) {
            return $this->failValidationErrors('Mata kuliah tidak ditemukan.');
        }

        // Cek apakah id_dosen ada
        $dosenModel = new DosenModel();
        if (!$dosenModel->find($input['id_dosen'])) {
            return $this->failValidationErrors('Dosen tidak ditemukan.');
        }

        $data = [
            'kelas'        => $input['kelas'],
            'hari'         => $input['hari'],
            'jam_mulai'    => $input['jam_mulai'],
            'jam_selesai'  => $input['jam_selesai'],
            'ruangan'      => $input['ruangan'],
            'tahun_ajaran' => $input['tahun_ajaran'],
            'semester'     => $input['semester'],
            'id_matkul'    => $input['id_matkul'],
            'id_dosen'     => $input['id_dosen'],
        ];

        $model->insert($data);
        $id = $model->getInsertID();

        return $this->respondCreated([
            'status'  => 201,
            'message' => 'Data jadwal berhasil ditambahkan',
            'data'    => $model->find($id),
        ]);
    }

    public function update($id = null)
    {
        $model  = new JadwalModel();
        $jadwal = $model->find($id);

        if (!$jadwal) {
            return $this->failNotFound('Data jadwal tidak ditemukan');
        }

        $input = $this->request->getJSON(true);

        if (!$input) {
            $input = $this->request->getRawInput();
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'kelas'         => 'required|max_length[10]',
            'hari'          => 'required|in_list[Senin,Selasa,Rabu,Kamis,Jumat,Sabtu]',
            'jam_mulai'     => 'required',
            'jam_selesai'   => 'required',
            'ruangan'       => 'required|max_length[50]',
            'tahun_ajaran'  => 'required|max_length[20]',
            'semester'      => 'required|integer',
            'id_matkul'     => 'required|integer',
            'id_dosen'      => 'required|integer',
        ], [
            'kelas'       => ['required' => 'Kelas wajib diisi.'],
            'hari'        => ['required' => 'Hari wajib diisi.', 'in_list' => 'Hari tidak valid.'],
            'jam_mulai'   => ['required' => 'Jam mulai wajib diisi.'],
            'jam_selesai' => ['required' => 'Jam selesai wajib diisi.'],
            'ruangan'     => ['required' => 'Ruangan wajib diisi.'],
            'tahun_ajaran'=> ['required' => 'Tahun ajaran wajib diisi.'],
            'semester'    => ['required' => 'Semester wajib diisi.', 'integer' => 'Semester harus berupa angka.'],
            'id_matkul'   => ['required' => 'Mata kuliah wajib dipilih.', 'integer' => 'ID matkul harus berupa angka.'],
            'id_dosen'    => ['required' => 'Dosen wajib dipilih.', 'integer' => 'ID dosen harus berupa angka.'],
        ]);

        if (!$validation->run($input)) {
            return $this->failValidationErrors($validation->getErrors());
        }

        // Cek apakah id_matkul ada
        $matkulModel = new MataKuliahModel();
        if (!$matkulModel->find($input['id_matkul'])) {
            return $this->failValidationErrors('Mata kuliah tidak ditemukan.');
        }

        // Cek apakah id_dosen ada
        $dosenModel = new DosenModel();
        if (!$dosenModel->find($input['id_dosen'])) {
            return $this->failValidationErrors('Dosen tidak ditemukan.');
        }

        $model->update($id, [
            'kelas'        => $input['kelas'],
            'hari'         => $input['hari'],
            'jam_mulai'    => $input['jam_mulai'],
            'jam_selesai'  => $input['jam_selesai'],
            'ruangan'      => $input['ruangan'],
            'tahun_ajaran' => $input['tahun_ajaran'],
            'semester'     => $input['semester'],
            'id_matkul'    => $input['id_matkul'],
            'id_dosen'     => $input['id_dosen'],
        ]);

        return $this->respond([
            'status'  => 200,
            'message' => 'Data jadwal berhasil diperbarui',
            'data'    => $model->find($id),
        ]);
    }

    public function delete($id = null)
    {
        $model  = new JadwalModel();
        $jadwal = $model->find($id);

        if (!$jadwal) {
            return $this->failNotFound('Data jadwal tidak ditemukan');
        }

        $model->delete($id);

        return $this->respondDeleted([
            'status'  => 200,
            'message' => 'Data jadwal berhasil dihapus',
        ]);
    }
}
