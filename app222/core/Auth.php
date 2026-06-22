<?php
class Auth
{
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user']['role'] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function login(array $user): void
    {
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'student_code' => $user['student_code'] ?? null,
            'role' => $user['role'],
        ];
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
        session_destroy();
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: ' . url('auth/login'));
            exit;
        }
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function isTechnician(): bool
    {
        return self::role() === 'technician';
    }

    public static function isStudent(): bool
    {
        return self::role() === 'student';
    }

    public static function requireAnyRole(array $roles): void
    {
        self::requireLogin();
        if (!in_array(self::role(), $roles, true)) {
            http_response_code(403);
            die('403 Forbidden - You do not have permission to access this action.');
        }
    }
}
