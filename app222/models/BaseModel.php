<?php
class BaseModel
{
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function count(string $table): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    }

    public function getAll(array $schema, ?array $where = null): array
    {
        $table = $schema['table'];
        $sql = "SELECT * FROM `$table`";
        $params = [];
        if ($where) {
            $conditions = [];
            foreach ($where as $field => $value) {
                $conditions[] = "`$field` = :$field";
                $params[":$field"] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= " ORDER BY id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(string $table, int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM `$table` WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function insert(string $table, array $data): int
    {
        $fields = array_keys($data);
        $columns = implode(', ', array_map(fn($f) => "`$f`", $fields));
        $placeholders = implode(', ', array_map(fn($f) => ":$f", $fields));
        $sql = "INSERT INTO `$table` ($columns) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        foreach ($data as $field => $value) {
            $stmt->bindValue(":$field", $value === '' ? null : $value);
        }
        $stmt->execute();
        return (int)$this->pdo->lastInsertId();
    }

    public function update(string $table, int $id, array $data): void
    {
        $sets = implode(', ', array_map(fn($f) => "`$f` = :$f", array_keys($data)));
        $sql = "UPDATE `$table` SET $sets WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        foreach ($data as $field => $value) {
            $stmt->bindValue(":$field", $value === '' ? null : $value);
        }
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function delete(string $table, int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM `$table` WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public function relationOptions(string $table, string $labelField, ?string $extraField = null): array
    {
        $columns = ['id', $labelField];
        if ($extraField) $columns[] = $extraField;
        $select = implode(', ', array_map(fn($c) => "`$c`", $columns));
        $stmt = $this->pdo->query("SELECT $select FROM `$table` ORDER BY `$labelField` ASC");
        $items = [];
        while ($row = $stmt->fetch()) {
            $label = $row[$labelField] ?? ('#' . $row['id']);
            if ($extraField && !empty($row[$extraField])) {
                $label = $row[$extraField] . ' - ' . $label;
            }
            $items[(string)$row['id']] = $label;
        }
        return $items;
    }

    public function exists(string $table, string $field, $value, ?int $exceptId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM `$table` WHERE `$field` = :value";
        $params = [':value' => $value];
        if ($exceptId) {
            $sql .= " AND id <> :id";
            $params[':id'] = $exceptId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }
}
