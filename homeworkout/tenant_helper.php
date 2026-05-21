<?php

function homeworkoutEnsureSessionStarted(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function homeworkoutRoleNameFromId($roleId): string {
    $map = [
        1 => 'utente',
        3 => 'amministratore',
        4 => 'super_admin'
    ];

    return $map[(int)$roleId] ?? 'utente';
}

function homeworkoutIsSuperAdmin(?int $roleId = null, ?string $roleName = null): bool {
    if ($roleName !== null && $roleName !== '') {
        return in_array($roleName, ['super_admin', 'superadmin', 'super-amministratore'], true);
    }

    return (int)$roleId === 4;
}

function homeworkoutGetBearerToken(): string {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!empty($authHeader) && stripos($authHeader, 'Bearer ') === 0) {
        return trim(substr($authHeader, 7));
    }

    return '';
}

function homeworkoutSetAuthSession(int $userId, ?int $tenantId, int $roleId, string $roleName, string $accessToken, ?string $refreshToken = null): void {
    homeworkoutEnsureSessionStarted();

    $_SESSION['utente_id'] = $userId;
    $_SESSION['tenant_id'] = $tenantId;
    $_SESSION['active_tenant_id'] = homeworkoutIsSuperAdmin($roleId, $roleName) ? ($_SESSION['active_tenant_id'] ?? $tenantId) : $tenantId;
    $_SESSION['access_token'] = $accessToken;

    if ($refreshToken !== null) {
        $_SESSION['refresh_token'] = $refreshToken;
    }

    $_SESSION['ruolo_id'] = $roleId;
    $_SESSION['ruolo_nome'] = $roleName;
}

function homeworkoutCurrentTenantId(?array $tokenData = null): ?int {
    homeworkoutEnsureSessionStarted();

    $sessionRoleName = $_SESSION['ruolo_nome'] ?? '';
    $sessionRoleId = isset($_SESSION['ruolo_id']) ? (int)$_SESSION['ruolo_id'] : null;

    if (homeworkoutIsSuperAdmin($sessionRoleId, $sessionRoleName)) {
        if (!empty($_SESSION['active_tenant_id'])) {
            return (int)$_SESSION['active_tenant_id'];
        }

        if ($tokenData && !empty($tokenData['tenant_id'])) {
            return (int)$tokenData['tenant_id'];
        }

        return null;
    }

    if (!empty($_SESSION['tenant_id'])) {
        return (int)$_SESSION['tenant_id'];
    }

    if ($tokenData && !empty($tokenData['tenant_id'])) {
        return (int)$tokenData['tenant_id'];
    }

    return null;
}

function homeworkoutTenantSql(string $column = 'tenant_id'): string {
    return sprintf('%s = :tenant_id', $column);
}
