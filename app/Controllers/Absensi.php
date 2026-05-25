<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\AbsensiModel;
use App\Models\KelasKuliahModel;
use App\Models\JadwalModel;

class Absensi extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $db = \Config\Database::connect();

        $data = $db->table('absensi')
            ->select('absensi.id_absensi, absensi.pertemuan_ke, absensi.tanggal')
            ->select('absensi.status, absensi.jam_absen')
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
            ->select('absensi.status, absensi.jam_absen')
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

    // Laporan absensi per dosen dengan filter hari ini/minggu/bulan
    public function laporanDosen($id_dosen = null)
    {
        $db     = \Config\Database::connect();
        $filter = $this->request->getGet('filter') ?? 'hari_ini';

        $query = $db->table('absensi')
            ->select('absensi.id_absensi, absensi.pertemuan_ke, absensi.tanggal')
            ->select('absensi.status, absensi.jam_absen')
            ->select('mahasiswa.npm, mahasiswa.nama, mahasiswa.kelas')
            ->select('mata_kuliah.nama_matkul, mata_kuliah.kode_matkul')
            ->select('jadwal.hari, jadwal.jam_mulai, jadwal.jam_selesai, jadwal.ruangan')
            ->join('kelas_kuliah', 'kelas_kuliah.id_kelas_kuliah = absensi.id_kelas_kuliah', 'left')
            ->join('mahasiswa', 'mahasiswa.id_mahasiswa = kelas_kuliah.id_mahasiswa', 'left')
            ->join('jadwal', 'jadwal.id_jadwal = kelas_kuliah.id_jadwal', 'left')
            ->join('mata_kuliah', 'mata_kuliah.id_matkul = jadwal.id_matkul', 'left')
            ->join('dosen', 'dosen.id_dosen = jadwal.id_dosen', 'left')
            ->where('dosen.id_dosen', $id_dosen);

        // Filter waktu
        if ($filter === 'hari_ini') {
            $query->where('absensi.tanggal', date('Y-m-d'));
        } elseif ($filter === 'minggu') {
            $query->where('absensi.tanggal >=', date('Y-m-d', strtotime('-7 days')));
        } elseif ($filter === 'bulan') {
            $query->where('absensi.tanggal >=', date('Y-m-d', strtotime('-30 days')));
        }

        $data = $query->get()->getResultArray();

        if (empty($data)) {
            return $this->failNotFound('Data absensi tidak ditemukan untuk dosen ini');
        }

        return $this->respond([
            'status' => 200,
            'filter' => $filter,
            'data'   => $data,
        ]);
    }

    // Laporan absensi mahasiswa sendiri
    public function laporanMahasiswa($id_mahasiswa = null)
    {
        $db     = \Config\Database::connect();
        $filter = $this->request->getGet('filter') ?? 'hari_ini';

        $query = $db->table('absensi')
            ->select('absensi.id_absensi, absensi.pertemuan_ke, absensi.tanggal')
            ->select('absensi.status, absensi.jam_absen')
            ->select('mata_kuliah.nama_matkul, mata_kuliah.kode_matkul')
            ->select('dosen.nama_dosen')
            ->select('jadwal.hari, jadwal.jam_mulai, jadwal.jam_selesai, jadwal.ruangan')
            ->join('kelas_kuliah', 'kelas_kuliah.id_kelas_kuliah = absensi.id_kelas_kuliah', 'left')
            ->join('mahasiswa', 'mahasiswa.id_mahasiswa = kelas_kuliah.id_mahasiswa', 'left')
            ->join('jadwal', 'jadwal.id_jadwal = kelas_kuliah.id_jadwal', 'left')
            ->join('mata_kuliah', 'mata_kuliah.id_matkul = jadwal.id_matkul', 'left')
            ->join('dosen', 'dosen.id_dosen = jadwal.id_dosen', 'left')
            ->where('mahasiswa.id_mahasiswa', $id_mahasiswa);

        // Filter waktu
        if ($filter === 'hari_ini') {
            $query->where('absensi.tanggal', date('Y-m-d'));
        } elseif ($filter === 'minggu') {
            $query->where('absensi.tanggal >=', date('Y-m-d', strtotime('-7 days')));
        } elseif ($filter === 'bulan') {
            $query->where('absensi.tanggal >=', date('Y-m-d', strtotime('-30 days')));
        }

        $data = $query->get()->getResultArray();

        if (empty($data)) {
            return $this->failNotFound('Data absensi tidak ditemukan untuk mahasiswa ini');
        }

        return $this->respond([
            'status' => 200,
            'filter' => $filter,
            'data'   => $data,
        ]);
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
            'pertemuan_ke'    => 'required|integer|greater_than[0]',
            'status'          => 'required|in_list[Hadir,Terlambat,Sakit,Izin]',
            'id_kelas_kuliah' => 'required|integer',
        ], [
            'pertemuan_ke' => [
                'required'     => 'Pertemuan ke wajib diisi.',
                'integer'      => 'Pertemuan ke harus berupa angka.',
                'greater_than' => 'Pertemuan ke harus lebih dari 0.',
            ],
            'status' => [
                'required' => 'Status wajib diisi.',
                'in_list'  => 'Status harus salah satu dari: Hadir, Terlambat, Sakit, Izin.',
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
        $kelasKuliah = $kelasKuliahModel->find($input['id_kelas_kuliah']);
        if (!$kelasKuliah) {
            return $this->failValidationErrors('Kelas kuliah tidak ditemukan.');
        }

        // Ambil jadwal berdasarkan kelas kuliah
        $jadwalModel = new JadwalModel();
        $jadwal = $jadwalModel->find($kelasKuliah['id_jadwal']);

        // Cek hari ini sesuai jadwal
        $hariIni = date('l'); // English day name
        $hariMap = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
        ];
        $hariIniIndonesia = $hariMap[$hariIni] ?? '';

        if ($hariIniIndonesia !== $jadwal['hari']) {
            return $this->fail('Hari ini bukan jadwal kuliah untuk kelas ini. Jadwal: ' . $jadwal['hari']);
        }

        // Cek jam sekarang antara jam_mulai dan jam_selesai
        $jamSekarang = date('H:i:s');
        $jamMulai    = $jadwal['jam_mulai'];
        $jamSelesai  = $jadwal['jam_selesai'];

        if ($jamSekarang < $jamMulai) {
            return $this->fail('Absensi belum dibuka. Kuliah dimulai pukul ' . $jamMulai);
        }

        if ($jamSekarang > $jamSelesai) {
            return $this->fail('Absensi sudah ditutup. Kuliah selesai pukul ' . $jamSelesai);
        }

        $today        = date('Y-m-d');
        $toleransi    = strtotime($today . ' ' . $jamMulai) + (15 * 60);
        $jamSekarangT = strtotime($today . ' ' . $jamSekarang);

        if ($input['status'] === 'Hadir' && $jamSekarangT > $toleransi) {
            $input['status'] = 'Terlambat';
        }

        $data = [
            'pertemuan_ke'    => $input['pertemuan_ke'],
            'tanggal'         => date('Y-m-d'),
            'status'          => $input['status'],
            'jam_absen'       => $jamSekarang,
            'id_kelas_kuliah' => $input['id_kelas_kuliah'],
        ];

        $model->insert($data);
        $id = $model->getInsertID();

        return $this->respondCreated([
            'status'  => 201,
            'message' => 'Absensi berhasil dicatat',
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
            'status' => 'required|in_list[Hadir,Terlambat,Sakit,Izin,Alpha]',
        ], [
            'status' => [
                'required' => 'Status wajib diisi.',
                'in_list'  => 'Status harus salah satu dari: Hadir, Terlambat, Sakit, Izin, Alpha.',
            ],
        ]);

        if (!$validation->run($input)) {
            return $this->failValidationErrors($validation->getErrors());
        }

        $model->update($id, [
            'status' => $input['status'],
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