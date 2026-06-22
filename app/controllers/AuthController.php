<?php
class AuthController extends Controller
{
    public function login(): void
    {
        if (Auth::check()) {
            $this->redirect('dashboard/index');
        }
        require BASE_PATH . '/app/views/auth/login.php';
    }

    public function register(): void
    {
        if (Auth::check()) {
            $this->redirect('dashboard/index');
        }
        $model = new AuthModel($this->pdo);
        $departments = $model->departments();
        require BASE_PATH . '/app/views/auth/register.php';
    }

    public function authenticate(): void
    {
        $this->requirePost();
        $identifier = trim($_POST['identifier'] ?? '');
        $password = $_POST['password'] ?? '';

        $model = new AuthModel($this->pdo);
        $user = $model->findByIdentifier($identifier);

        if ($user && password_verify($password, $user['password_hash'])) {
            Auth::login($user);
            $this->redirect('dashboard/index');
        }

        $_SESSION['old_identifier'] = $identifier;
        $_SESSION['login_error'] = 'Invalid login information or password.';
        $this->redirect('auth/login');
    }

    public function storeRegister(): void
    {
        $this->requirePost();
        $model = new AuthModel($this->pdo);

        $role = $_POST['role'] ?? '';
        $studentCode = strtoupper(trim($_POST['student_code'] ?? ''));

        $data = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'student_code' => $studentCode,
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'role' => $role,
            'department_id' => $_POST['department_id'] ?? null,
            'phone' => trim($_POST['phone'] ?? ''),
        ];

        $_SESSION['register_old'] = $data;

        try {
            if ($data['full_name'] === '' || $data['email'] === '' || $data['password'] === '' || $data['role'] === '') {
                throw new Exception('Please complete all required fields.');
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email format is invalid.');
            }
            if (!in_array($data['role'], ['student', 'technician'], true)) {
                throw new Exception('Only student and technician accounts can self-register.');
            }
            if ($data['role'] === 'student' && $data['student_code'] === '') {
                throw new Exception('Student ID is required for student accounts.');
            }
            if ($data['role'] !== 'student') {
                $data['student_code'] = null;
            }
            if (strlen($data['password']) < 6) {
                throw new Exception('Password must contain at least 6 characters.');
            }
            if ($data['password'] !== $data['confirm_password']) {
                throw new Exception('Password confirmation does not match.');
            }
            if ($model->findByEmail($data['email'])) {
                throw new Exception('This email is already registered.');
            }
            if ($data['student_code'] && $model->studentCodeExists($data['student_code'])) {
                throw new Exception('This student ID is already registered.');
            }

            $model->createRegisteredUser([
                'department_id' => $data['department_id'] ?: null,
                'student_code' => $data['student_code'],
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role' => $data['role'],
                'phone' => $data['phone'],
                'status' => 'active',
            ]);

            unset($_SESSION['register_old']);
            $_SESSION['login_error'] = 'Account created successfully. You can now sign in.';
            $this->redirect('auth/login');
        } catch (Throwable $e) {
            $_SESSION['register_error'] = $e->getMessage();
            $this->redirect('auth/register');
        }
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: ' . url('auth/login'));
        exit;
    }
}
