<?php

function ascom_is_docker_environment(): bool
{
    return getenv('DOCKER_ENV') === 'true' || file_exists('/.dockerenv');
}

function ascom_db_config(): array
{
    // Check for docker-compose style env vars first, then legacy
    $host = getenv('DB_HOST') ?: (ascom_is_docker_environment() ? 'db' : 'localhost');
    $database = getenv('DB_DATABASE') ?: getenv('ASCOM_DB_NAME') ?: 'ascom_db';
    $username = getenv('DB_USERNAME') ?: getenv('ASCOM_DB_USER') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: getenv('ASCOM_DB_PASSWORD') ?: '';

    return [
        'host' => $host,
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
