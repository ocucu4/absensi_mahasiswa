<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Auth routes
$routes->post('register', 'Auth::register');
$routes->post('login',    'Auth::login');
$routes->post('logout', 'Auth::logout');

$routes->group('api', ['filter' => 'authapi'], static function ($routes) {

    // Dosen
    $routes->get('dosen',            'Dosen::index');
    $routes->get('dosen/(:num)',     'Dosen::show/$1');
    $routes->post('dosen',           'Dosen::create');
    $routes->put('dosen/(:num)',     'Dosen::update/$1');
    $routes->delete('dosen/(:num)', 'Dosen::delete/$1');

    // Jurusan
    $routes->get('jurusan',            'Jurusan::index');
    $routes->get('jurusan/(:num)',     'Jurusan::show/$1');
    $routes->post('jurusan',           'Jurusan::create');
    $routes->put('jurusan/(:num)',     'Jurusan::update/$1');
    $routes->delete('jurusan/(:num)', 'Jurusan::delete/$1');

    // Mahasiswa
    $routes->get('mahasiswa',            'Mahasiswa::index');
    $routes->get('mahasiswa/(:num)',     'Mahasiswa::show/$1');
    $routes->post('mahasiswa',           'Mahasiswa::create');
    $routes->put('mahasiswa/(:num)',     'Mahasiswa::update/$1');
    $routes->delete('mahasiswa/(:num)', 'Mahasiswa::delete/$1');

    // MataKuliah
    $routes->get('matkul',            'MataKuliah::index');
    $routes->get('matkul/(:num)',     'MataKuliah::show/$1');
    $routes->post('matkul',           'MataKuliah::create');
    $routes->put('matkul/(:num)',     'MataKuliah::update/$1');
    $routes->delete('matkul/(:num)', 'MataKuliah::delete/$1');

    // Jadwal
    $routes->get('jadwal',            'Jadwal::index');
    $routes->get('jadwal/dosen/(:num)',     'Jadwal::jadwalDosen/$1');
    $routes->get('jadwal/mahasiswa/(:num)', 'Jadwal::jadwalMahasiswa/$1'); 
    $routes->get('jadwal/(:num)',     'Jadwal::show/$1');
    $routes->post('jadwal',           'Jadwal::create');
    $routes->put('jadwal/(:num)',     'Jadwal::update/$1');
    $routes->delete('jadwal/(:num)', 'Jadwal::delete/$1');

    // KelasKuliah
    $routes->get('kelas-kuliah',            'KelasKuliah::index');
    $routes->get('kelas-kuliah/(:num)',     'KelasKuliah::show/$1');
    $routes->post('kelas-kuliah',           'KelasKuliah::create');
    $routes->delete('kelas-kuliah/(:num)', 'KelasKuliah::delete/$1');

    // Absensi
    $routes->get('absensi',                      'Absensi::index');
    $routes->get('absensi/dosen/(:num)',         'Absensi::laporanDosen/$1');
    $routes->get('absensi/mahasiswa/(:num)',     'Absensi::laporanMahasiswa/$1');
    $routes->get('absensi/(:num)',               'Absensi::show/$1');
    $routes->post('absensi',                     'Absensi::create');
    $routes->put('absensi/(:num)',               'Absensi::update/$1');
    $routes->delete('absensi/(:num)',            'Absensi::delete/$1');
});