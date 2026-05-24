<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\AbsensiModel;
use App\Models\KelasKuliahModel;

class Absensi extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $db = \Config\Database::connect();

        $data = $db->table('absensi')
            ->select('absensi.id_absensi, absensi.pertemuan_ke, absensi.tanggal')
            ->select('absensi.status, absensi.jam_absen, absensi.keterangan')
            ->select('mahasiswa.npm, mahasiswa.nama, mahasiswa.kelas')
            ->select('mata_kuliah.nama_matkul, mata_kuliah.kode_matkul')
            ->select('dosen.nama_dosen')
            ->select('jadwal.hari, jadwal.jam_mulai, jadwal.jam_selesai, jadwal.ruangan')
            ->join('kelas_kuliah', 'kelas_kuliah.id_kelas_kuliah = absensi.id_kelas_kuliah', 'left')
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

        $data = $db->table('absensi')
            ->select('absensi.id_absensi, absensi.pertemuan_ke, absensi.tanggal')
            ->select('absensi.status, absensi.jam_absen, absensi.keterangan')
            ->select('mahasiswa.npm, mahasiswa.nama, mahasiswa.kelas')
            ->select('mata_kuliah.nama_matkul, mata_kuliah.kode_matkul')
            ->select('dosen.nama_dosen')
            ->select('jadwal.hari, jadwal.jam_mulai, jadwal.jam_selesai, jadwal.ruangan')
            ->join('kelas_kuliah', 'kelas_kuliah.id_kelas_kuliah = absensi.id_kelas_kuliah', 'left')
            ->join('mahasiswa', 'mahasiswa.id_mahasiswa = kelas_kuliah.id_mahasiswa', 'left')
            ->join('jadwal', 'jadwal.id_jadwal = kelas_kuliah.id_jadwal', 'left')
            ->join('mata_kuliah', 'mata_kuliah.id_matkul = jadwal.id_matkul', 'left')
            ->join('dosen', 'dosen.id_dosen = jadwal.id_dosen', 'left')
            ->where('absensi.id_absensi', $id)
            ->get()
            ->getRowArray();

        if (!$data) {
            return $this->failNotFound('Data absensi tidak ditemukan');
        }

        return $this->respond($data);
    }

    // Laporan absensi per dosen (dosen lihat absensi mahasiswa di matkulnya)
    public function laporanDosen($id_dosen = null)
    {
        $db = \Config\Database::connect();

        $data = $db->table('absensi')
            ->select('absensi.id_absensi, absensi.pertemuan_ke, absensi.tanggal')
            ->select('absensi.status, absensi.jam_absen, absensi.keterangan')
            ->select('mahasiswa.npm, mahasiswa.nama, mahasiswa.kelas')
            ->select('mata_kuliah.nama_matkul, mata_kuliah.kode_matkul')
            ->select('jadwal.hari, jadwal.jam_mulai, jadwal.jam_selesai, jadwal.ruangan')
            ->join('kelas_kuliah', 'kelas_kuliah.id_kelas_kuliah = absensi.id_kelas_kuliah', 'left')
            ->join('mahasiswa', 'mahasiswa.id_mahasiswa = kelas_kuliah.id_mahasiswa', 'left')
            ->join('jadwal', 'jadwal.id_jadwal = kelas_kuliah.id_jadwal', 'left')
            ->join('mata_kuliah', 'mata_kuliah.id_matkul = jadwal.id_matkul', 'left')
            ->join('dosen', 'dosen.id_dosen = jadwal.id_dosen', 'left')
            ->where('dosen.id_dosen', $id_dosen)
            ->get()
            ->getResultArray();

        if (empty($data)) {
            return $this->failNotFound('Data absensi tidak ditemukan untuk dosen ini');
        }

        return $this->respond($data);
    }

    // Absensi mahasiswa (mahasiswa lihat absensinya sendiri)
    public function laporanMahasiswa($id_mahasiswa = null)
    {
        $db = \Config\Database::connect();

        $data = $db->table('absensi')
            ->select('absensi.id_absensi, absensi.pertemuan_ke, absensi.tanggal')
            ->select('absensi.status, absensi.jam_absen, absensi.keterangan')
            ->select('mata_kuliah.nama_matkul, mata_kuliah.kode_matkul')
            ->select('dosen.nama_dosen')
            ->select('jadwal.hari, jadwal.jam_mulai, jadwal.jam_selesai, jadwal.ruangan')
            ->join('kelas_kuliah', 'kelas_kuliah.id_kelas_kuliah = absensi.id_kelas_kuliah', 'left')
            ->join('mahasiswa', 'mahasiswa.id_mahasiswa = kelas_kuliah.id_mahasiswa', 'left')
            ->join('jadwal', 'jadwal.id_jadwal = kelas_kuliah.id_jadwal', 'left')
            ->join('mata_kuliah', 'mata_kuliah.id_matkul = jadwal.id_matkul', 'left')
            ->join('dosen', 'dosen.id_dosen = jadwal.id_dosen', 'left')
            ->where('mahasiswa.id_mahasiswa', $id_mahasiswa)
            ->get()
            ->getResultArray();

        if (empty($data)) {
            return $this->failNotFound('Data absensi tidak ditemukan untuk mahasiswa ini');
        }

        return $this->respond($data);
    }

    public function create()
    {
        $model = new AbsensiModel();
        $input = $this->request->getJSON(true);

        if (!$input) {
            $input = $this->request->getPost();
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'pertemuan_ke'   => 'required|integer|greater_than[0]',
            'tanggal'        => 'required|valid_date',
            'status'         => 'required|in_list[Hadir,Izin,Sakit,Alpha]',
            'jam_absen'      => 'required',
            'keterangan'     => 'permit_empty|max_length[255]',
            'id_kelas_kuliah'=> 'required|integer',
        ], [
            'pertemuan_ke' => [
                'required'     => 'Pertemuan ke wajib diisi.',
                'integer'      => 'Pertemuan ke harus berupa angka.',
                'greater_than' => 'Pertemuan ke harus lebih dari 0.',
            ],
            'tanggal' => [
                'required'   => 'Tanggal wajib diisi.',
                'valid_date' => 'Format tanggal tidak valid.',
            ],
            'status' => [
                'required' => 'Status wajib diisi.',
                'in_list'  => 'Status harus salah satu dari: Hadir, Izin, Sakit, Alpha.',
            ],
            'jam_absen' => [
                'required' => 'Jam absen wajib diisi.',
            ],
            'keterangan' => [
                'max_length' => 'Keterangan maksimal 255 karakter.',
            ],
            'id_kelas_kuliah' => [
                'required' => 'Kelas kuliah wajib dipilih.',
                'integer'  => 'ID kelas kuliah harus berupa angka.',
            ],
        ]);

        if (!$validation->run($input)) {
            return $this->failValidationErrors($validation->getErrors());
        }

        // Cek apakah id_kelas_kuliah ada
        $kelasKuliahModel = new KelasKuliahModel();
        if (!$kelasKuliahModel->find($input['id_kelas_kuliah'])) {
            return $this->failValidationErrors('Kelas kuliah tidak ditemukan.');
        }

        $data = [
            'pertemuan_ke'    => $input['pertemuan_ke'],
            'tanggal'         => $input['tanggal'],
            'status'          => $input['status'],
            'jam_absen'       => $input['jam_absen'],
            'keterangan'      => $input['keterangan'] ?? null,
            'id_kelas_kuliah' => $input['id_kelas_kuliah'],
        ];

        $model->insert($data);
        $id = $model->getInsertID();

        return $this->respondCreated([
            'status'  => 201,
            'message' => 'Data absensi berhasil ditambahkan',
            'data'    => $model->find($id),
        ]);
    }

    public function update($id = null)
    {
        $model   = new AbsensiModel();
        $absensi = $model->find($id);

        if (!$absensi) {
            return $this->failNotFound('Data absensi tidak ditemukan');
        }

        $input = $this->request->getJSON(true);

        if (!$input) {
            $input = $this->request->getRawInput();
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'pertemuan_ke'   => 'required|integer|greater_than[0]',
            'tanggal'        => 'required|valid_date',
            'status'         => 'required|in_list[Hadir,Izin,Sakit,Alpha]',
            'jam_absen'      => 'required',
            'keterangan'     => 'permit_empty|max_length[255]',
            'id_kelas_kuliah'=> 'required|integer',
        ], [
            'pertemuan_ke' => [
                'required'     => 'Pertemuan ke wajib diisi.',
                'integer'      => 'Pertemuan ke harus berupa angka.',
                'greater_than' => 'Pertemuan ke harus lebih dari 0.',
            ],
            'tanggal' => [
                'required'   => 'Tanggal wajib diisi.',
                'valid_date' => 'Format tanggal tidak valid.',
            ],
            'status' => [
                'required' => 'Status wajib diisi.',
                'in_list'  => 'Status harus salah satu dari: Hadir, Izin, Sakit, Alpha.',
            ],
            'jam_absen' => [
                'required' => 'Jam absen wajib diisi.',
            ],
            'keterangan' => [
                'max_length' => 'Keterangan maksimal 255 karakter.',
            ],
            'id_kelas_kuliah' => [
                'required' => 'Kelas kuliah wajib dipilih.',
                'integer'  => 'ID kelas kuliah harus berupa angka.',
            ],
        ]);

        if (!$validation->run($input)) {
            return $this->failValidationErrors($validation->getErrors());
        }

        // Cek apakah id_kelas_kuliah ada
        $kelasKuliahModel = new KelasKuliahModel();
        if (!$kelasKuliahModel->find($input['id_kelas_kuliah'])) {
            return $this->failValidationErrors('Kelas kuliah tidak ditemukan.');
        }

        $model->update($id, [
            'pertemuan_ke'    => $input['pertemuan_ke'],
            'tanggal'         => $input['tanggal'],
            'status'          => $input['status'],
            'jam_absen'       => $input['jam_absen'],
            'keterangan'      => $input['keterangan'] ?? null,
            'id_kelas_kuliah' => $input['id_kelas_kuliah'],
        ]);

        return $this->respond([
            'status'  => 200,
            'message' => 'Data absensi berhasil diperbarui',
            'data'    => $model->find($id),
        ]);
    }

    public function delete($id = null)
    {
        $model   = new AbsensiModel();
        $absensi = $model->find($id);

        if (!$absensi) {
            return $this->failNotFound('Data absensi tidak ditemukan');
        }

        $model->delete($id);

        return $this->respondDeleted([
            'status'  => 200,
            'message' => 'Data absensi berhasil dihapus',
        ]);
    }
}