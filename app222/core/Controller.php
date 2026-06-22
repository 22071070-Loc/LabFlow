<?php
class Controller
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    protected function view(string $view, array $data = []): void
    {
        extract($data);
        require BASE_PATH . '/app/views/layouts/header.php';
        require BASE_PATH . '/app/views/' . $view . '.php';
        require BASE_PATH . '/app/views/layouts/footer.php';
    }

    protected function redirect(string $route, array $params = []): void
    {
        header('Location: ' . url($route, $params));
        exit;
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    protected function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Method Not Allowed');
        }
    }
}
