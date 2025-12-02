<?php
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private User $users;

    public function __construct(User $users)
    {
        $this->users = $users;
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
        $user = $this->users->findByUsername($username);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->respond(['error' => 'Invalid credentials'], 401);
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $this->respond(['ok' => true, 'username' => $user['username']]);
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
