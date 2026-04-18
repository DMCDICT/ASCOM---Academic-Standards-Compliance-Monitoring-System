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
