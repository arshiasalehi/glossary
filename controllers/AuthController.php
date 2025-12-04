<?php
require_once __DIR__ . '/../config.php';

class AuthController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login(array $input): void
    {
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';
        if ($username === '' || $password === '') {
            $this->respond(['error' => 'Username and password required'], 400);
        }
        $adminUser = env('ADMIN_USER', 'admin');
        $adminPass = env('ADMIN_PASS', 'admin123');
        if (!hash_equals($adminUser, $username) || !hash_equals($adminPass, $password)) {
            $this->respond(['error' => 'Invalid credentials'], 401);
        }
        $_SESSION['user_id'] = $adminUser;
        $_SESSION['username'] = $adminUser;
        $this->respond(['ok' => true, 'username' => $adminUser]);
    }

    public function requireAuth(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            $this->respond(['error' => 'Unauthorized'], 401);
        }
    }

    private function respond(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }
}
