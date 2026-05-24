<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Auth routes
$routes->post('register', 'Auth::register');
$routes->post('login',    'Auth::login');
$routes->post('logout',   'Auth::logout');

// Dosen routes
$routes->get('dosen',            'Dosen::index');
$routes->get('dosen/(:num)',     'Dosen::show/$1');
$routes->post('dosen',           'Dosen::create');
$routes->put('dosen/(:num)',     'Dosen::update/$1');
$routes->delete('dosen/(:num)', 'Dosen::delete/$1');
