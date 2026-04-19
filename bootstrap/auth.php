<?php

function ascom_clear_role_flags(): void
{
    unset(
        $_SESSION['teacher_logged_in'],
        $_SESSION['dean_logged_in'],
        $_SESSION['librarian_logged_in'],
        $_SESSION['admin_qa_logged_in']
    );
}

function ascom_set_selected_role(array $role): void
{
    ascom_clear_role_flags();

    $_SESSION['selected_role'] = $role;
    $_SESSION['user_role'] = $role['type'];

    switch ($role['type']) {
        case 'teacher':
            $_SESSION['teacher_logged_in'] = true;
            break;
        case 'dean':
            $_SESSION['dean_logged_in'] = true;
            break;
        case 'librarian':
            $_SESSION['librarian_logged_in'] = true;
            break;
        case 'quality_assurance':
            $_SESSION['admin_qa_logged_in'] = true;
            break;
    }
}

function ascom_find_user_role(string $roleType): ?array
{
    $roles = $_SESSION['user_roles'] ?? [];

    if (!is_array($roles)) {
        return null;
    }

    foreach ($roles as $role) {
        if (($role['type'] ?? null) === $roleType) {
            return $role;
        }
    }

    return null;
}

function ascom_authenticated_for_regular_user(): bool
{
    return !empty($_SESSION['is_authenticated']) && !empty($_SESSION['user_id']);
}

function ascom_require_super_admin(string $redirectPath = '../super_admin_login.php'): void
{
    // The calling script should have included super_admin_session_config.php already
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Security Hardening: Ensure no regular user flags exist in a Super Admin session
    if (!empty($_SESSION['user_roles']) || !empty($_SESSION['selected_role'])) {
        ascom_clear_role_flags();
        unset($_SESSION['user_roles'], $_SESSION['selected_role']);
    }

    // Check for explicit super_admin_logged_in flag AND the user_role string
    // Also check 'super_admin_session' to ensure session came from the dedicated login
    if (empty($_SESSION['super_admin_logged_in']) || 
        ($_SESSION['user_role'] ?? null) !== 'super_admin' ||
        empty($_SESSION['super_admin_session'])) {
        
        header("Location: {$redirectPath}");
        exit();
    }
}

function ascom_require_role(string $roleType, string $redirectPath = '../user_login.php'): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Security Hardening: Ensure no super admin flags exist in a regular user session
    if (!empty($_SESSION['super_admin_logged_in']) || !empty($_SESSION['super_admin_session'])) {
        $_SESSION = [];
        header("Location: {$redirectPath}");
        exit();
    }

    if (!ascom_authenticated_for_regular_user()) {
        header("Location: {$redirectPath}");
        exit();
    }

    $selectedRole = $_SESSION['selected_role'] ?? null;
    if (is_array($selectedRole) && ($selectedRole['type'] ?? null) === $roleType) {
        return;
    }

    $matchingRole = ascom_find_user_role($roleType);
    $allRoles = $_SESSION['user_roles'] ?? [];
    $roleCount = is_array($allRoles) ? count($allRoles) : 0;

    if ($matchingRole !== null && $roleCount === 1) {
        ascom_set_selected_role($matchingRole);
        return;
    }

    $flagMap = [
        'teacher' => 'teacher_logged_in',
        'dean' => 'dean_logged_in',
        'librarian' => 'librarian_logged_in',
        'quality_assurance' => 'admin_qa_logged_in',
    ];

    $flagName = $flagMap[$roleType] ?? null;
    if ($matchingRole !== null && $flagName !== null && !empty($_SESSION[$flagName])) {
        ascom_set_selected_role($matchingRole);
        return;
    }

    header("Location: {$redirectPath}");
    exit();
}

function ascom_password_is_hash(?string $storedPassword): bool
{
    if (!is_string($storedPassword) || $storedPassword === '') {
        return false;
    }

    return password_get_info($storedPassword)['algo'] !== null;
}

function ascom_verify_password_with_migration(
    string $inputPassword,
    ?string $storedPassword,
    callable $persistHash
): bool {
    if (!is_string($storedPassword) || $storedPassword === '') {
        return false;
    }

    if (ascom_password_is_hash($storedPassword)) {
        if (!password_verify($inputPassword, $storedPassword)) {
            return false;
        }

        if (password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
            $persistHash(password_hash($inputPassword, PASSWORD_DEFAULT));
        }

        return true;
    }

    if (!hash_equals($storedPassword, $inputPassword)) {
        return false;
    }

    $persistHash(password_hash($inputPassword, PASSWORD_DEFAULT));
    return true;
}
