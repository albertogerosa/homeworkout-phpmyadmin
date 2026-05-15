<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use PHPUnit\Framework\TestCase;

final class TenantHelperTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [];
        $_SESSION = [];
    }

    public function testRoleNameFromIdReturnsExpectedValues(): void
    {
        $this->assertSame('utente', homeworkoutRoleNameFromId(1));
        $this->assertSame('allenatore', homeworkoutRoleNameFromId(2));
        $this->assertSame('amministratore', homeworkoutRoleNameFromId(3));
        $this->assertSame('super_admin', homeworkoutRoleNameFromId(4));
        $this->assertSame('utente', homeworkoutRoleNameFromId(999));
    }

    public function testIsSuperAdminAcceptsRoleIdAndKnownNames(): void
    {
        $this->assertTrue(homeworkoutIsSuperAdmin(4, null));
        $this->assertTrue(homeworkoutIsSuperAdmin(null, 'super_admin'));
        $this->assertTrue(homeworkoutIsSuperAdmin(null, 'superadmin'));
        $this->assertFalse(homeworkoutIsSuperAdmin(2, 'allenatore'));
    }

    public function testGetBearerTokenReadsAuthorizationHeader(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer my-token';
        $this->assertSame('my-token', homeworkoutGetBearerToken());

        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic abc';
        $this->assertSame('', homeworkoutGetBearerToken());
    }

    public function testSetAuthSessionStoresExpectedFields(): void
    {
        homeworkoutSetAuthSession(15, 3, 2, 'allenatore', 'access123', 'refresh123');

        $this->assertSame(15, $_SESSION['utente_id']);
        $this->assertSame(3, $_SESSION['tenant_id']);
        $this->assertSame(3, $_SESSION['active_tenant_id']);
        $this->assertSame('access123', $_SESSION['access_token']);
        $this->assertSame('refresh123', $_SESSION['refresh_token']);
        $this->assertSame(2, $_SESSION['ruolo_id']);
        $this->assertSame('allenatore', $_SESSION['ruolo_nome']);
    }

    public function testCurrentTenantIdForSuperAdminUsesActiveTenantThenTokenFallback(): void
    {
        $_SESSION['ruolo_id'] = 4;
        $_SESSION['ruolo_nome'] = 'super_admin';
        $_SESSION['active_tenant_id'] = 11;

        $this->assertSame(11, homeworkoutCurrentTenantId(['tenant_id' => 8]));

        unset($_SESSION['active_tenant_id']);
        $this->assertSame(8, homeworkoutCurrentTenantId(['tenant_id' => 8]));
        $this->assertNull(homeworkoutCurrentTenantId(null));
    }

    public function testCurrentTenantIdForNonSuperAdminUsesSessionThenTokenFallback(): void
    {
        $_SESSION['ruolo_id'] = 2;
        $_SESSION['ruolo_nome'] = 'allenatore';
        $_SESSION['tenant_id'] = 5;

        $this->assertSame(5, homeworkoutCurrentTenantId(['tenant_id' => 9]));

        unset($_SESSION['tenant_id']);
        $this->assertSame(9, homeworkoutCurrentTenantId(['tenant_id' => 9]));
        $this->assertNull(homeworkoutCurrentTenantId([]));
    }

    public function testTenantSqlGeneratesPlaceholderCondition(): void
    {
        $this->assertSame('tenant_id = :tenant_id', homeworkoutTenantSql());
        $this->assertSame('u.tenant_id = :tenant_id', homeworkoutTenantSql('u.tenant_id'));
    }
}
