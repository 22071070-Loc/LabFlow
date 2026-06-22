<?php
class AuthModel extends BaseModel
{
    public function findByIdentifier(string $identifier): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM users
            WHERE status = 'active'
              AND (email = :email OR student_code = :student_code)
            LIMIT 1
        ");
        $stmt->execute([
            ':email' => $identifier,
            ':student_code' => strtoupper($identifier)
        ]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email AND status = 'active'");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function studentCodeExists(string $studentCode): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE student_code = :student_code");
        $stmt->execute([':student_code' => $studentCode]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function createRegisteredUser(array $data): int
    {
        return $this->insert('users', $data);
    }

    public function departments(): array
    {
        $stmt = $this->pdo->query("SELECT id, department_name FROM departments ORDER BY department_name ASC");
        return $stmt->fetchAll();
    }
}
