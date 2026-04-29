<?php

function ascom_is_docker_environment(): bool
{
    // Check various common docker env indications
    if (getenv('DOCKER_ENV') === 'true' || isset($_SERVER['DOCKER_ENV'])) {
        return true;
    }
    if (file_exists('/.dockerenv')) {
        return true;
    }
    // Often DB_HOST=db is a strong giveaway we are in docker compose
    if ((getenv('DB_HOST') ?: (isset($_SERVER['DB_HOST']) ? $_SERVER['DB_HOST'] : '')) === 'db') {
        return true;
    }
    // If not found above, try to see if 'db' host is resolvable (Docker's internal DNS)
    if (gethostbyname('db') !== 'db') {
        return true;
    }
    return false;
}

function ascom_db_config(): array
{
    // Try to get host from Apache env mapped variables if getenv fails
    $envHost = getenv('DB_HOST') ?: (isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : (isset($_SERVER['DB_HOST']) ? $_SERVER['DB_HOST'] : ''));
    
    // Fallback: If not found, guess based on docker files
    if (!$envHost) {
        $envHost = ascom_is_docker_environment() ? 'db' : 'localhost';
    }

    $database = getenv('DB_DATABASE') ?: (isset($_SERVER['DB_DATABASE']) ? $_SERVER['DB_DATABASE'] : 'ascom_db');
    $username = getenv('DB_USERNAME') ?: (isset($_SERVER['DB_USERNAME']) ? $_SERVER['DB_USERNAME'] : 'ascom_user');
    $password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : (isset($_SERVER['DB_PASSWORD']) ? $_SERVER['DB_PASSWORD'] : 'ascom_password_secure_123');

    return [
        'host' => $envHost,
        'database' => $database,
        'username' => $username,
        'password' => $password,
    ];
}

function ascom_get_mysqli(): mysqli
{
    static $conn = null;

    if ($conn instanceof mysqli) {
        return $conn;
    }

    $config = ascom_db_config();

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $conn = new mysqli(
        $config['host'],
        $config['username'],
        $config['password'],
        $config['database']
    );
    $conn->set_charset('utf8mb4');

    return $conn;
}

function ascom_get_pdo(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = ascom_db_config();
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $config['host'],
        $config['database']
    );

    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function ascom_table_has_column(PDO $pdo, string $tableName, string $columnName): bool
{
    static $cache = [];

    $cacheKey = strtolower($tableName . '.' . $columnName);
    if (array_key_exists($cacheKey, $cache)) {
        return $cache[$cacheKey];
    }

    $sql = "
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = :table_name
          AND COLUMN_NAME = :column_name
        LIMIT 1
    ";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':table_name' => $tableName,
            ':column_name' => $columnName,
        ]);
        $cache[$cacheKey] = (bool) $stmt->fetchColumn();
    } catch (Throwable $e) {
        // If information_schema is unavailable for any reason, assume "no" to keep queries conservative.
        $cache[$cacheKey] = false;
    }

    return $cache[$cacheKey];
}

function ascom_user_roles_role_predicate(PDO $pdo, string $userRolesAlias, string $roleName): string
{
    // Supports both schemas:
    // - user_roles.role_name (legacy)
    // - user_roles.role_id referencing roles.id (normalized)
    if (ascom_table_has_column($pdo, 'user_roles', 'role_name')) {
        return "{$userRolesAlias}.role_name = " . $pdo->quote($roleName);
    }

    if (ascom_table_has_column($pdo, 'user_roles', 'role_id') && ascom_table_has_column($pdo, 'roles', 'role_name')) {
        return "{$userRolesAlias}.role_id = (SELECT id FROM roles WHERE role_name = " . $pdo->quote($roleName) . " LIMIT 1)";
    }

    // Fallback: can't reliably filter by role.
    return '1=1';
}

function ascom_user_roles_active_predicate(PDO $pdo, string $userRolesAlias): string
{
    if (ascom_table_has_column($pdo, 'user_roles', 'is_active')) {
        return "{$userRolesAlias}.is_active = 1";
    }
    return '1=1';
}
