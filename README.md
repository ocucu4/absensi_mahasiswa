# Absensi Mahasiswa - REST API

A REST API application for managing student attendance, built with CodeIgniter 4.

## About This Project

This project is a REST API for a student attendance management system. It was developed as a major assignment for the Programming III course using CodeIgniter 4, Postman, and MySQL Workbench.

**Author:** Kelompok 4

## Tech Stack

- **Framework:** CodeIgniter 4
- **Language:** PHP 8.2+
- **Database:** MySQL
- **Tools:** Postman, MySQL Workbench

## Features

- User Authentication (Register, Login, Logout) with Bearer Token
- Student Management
- Lecturer Management
- Course & Schedule Management
- Class Enrollment Management
- Attendance Management
- Role-based Access (Admin, Dosen, Mahasiswa)

## Requirements

PHP version 8.2 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)
- json (enabled by default)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php)
- [libcurl](http://php.net/manual/en/curl.requirements.php)

## Setup

Copy `env` to `.env` and configure your app settings including baseURL and database credentials.

## Authentication

All `/api/*` endpoints are protected with Bearer Token authentication.

Include the token in the request header:
```
Authorization: Bearer your_token_here
```

Get the token by logging in via `POST /login`.

## License

This project is for educational purposes only.