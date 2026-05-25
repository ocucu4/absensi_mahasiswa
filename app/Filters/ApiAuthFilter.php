<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use App\Models\UserModel;

class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        // Cek apakah header Authorization ada
        if (!$authHeader) {
            return Services::response()
                ->setStatusCode(401)
                ->setJSON([
                    'status'  => 401,
                    'error'   => 401,
                    'message' => 'Token tidak ditemukan. Silahkan login terlebih dahulu.',
                ]);
        }

        // Cek format Bearer Token
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return Services::response()
                ->setStatusCode(401)
                ->setJSON([
                    'status'  => 401,
                    'error'   => 401,
                    'message' => 'Format token tidak valid.',
                ]);
        }

        $token     = $matches[1];
        $userModel = new UserModel();
        $user      = $userModel->where('token', $token)->first();

        // Cek apakah token valid
        if (!$user) {
            return Services::response()
                ->setStatusCode(401)
                ->setJSON([
                    'status'  => 401,
                    'error'   => 401,
                    'message' => 'Token tidak valid atau sudah kadaluarsa. Silahkan login kembali.',
                ]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada proses setelah request
    }
}