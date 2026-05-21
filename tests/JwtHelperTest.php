<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use PHPUnit\Framework\TestCase;

final class JwtHelperTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [];
        $_SESSION = [];
    }

    public function testGenerateAndValidateJwtWithTenantAndRole(): void
    {
        $token = generateJWT(42, 5, 3, 7, 'Tenant A');
        $payload = validateJWT($token);

        $this->assertIsArray($payload);
        $this->assertSame(42, $payload['user_id']);
        $this->assertSame(3, $payload['role_id']);
        $this->assertSame(7, $payload['tenant_id']);
        $this->assertSame('Tenant A', $payload['tenant_name']);
        $this->assertGreaterThan(time(), $payload['exp']);
    }

    public function testValidateJwtRejectsMalformedToken(): void
    {
        $this->assertFalse(validateJWT('not.a.valid.jwt.with.too.many.parts'));
        $this->assertFalse(validateJWT('invalid'));
    }

    public function testValidateJwtRejectsTamperedSignature(): void
    {
        $token = generateJWT(99, 5);
        $parts = explode('.', $token);
        $parts[1] = base64UrlEncode(json_encode([
            'user_id' => 999,
            'iat' => time(),
            'exp' => time() + 300,
        ]));

        $tampered = implode('.', $parts);

        $this->assertFalse(validateJWT($tampered));
    }

    public function testValidateJwtRejectsExpiredToken(): void
    {
        $expiredToken = generateJWT(77, -1);

        $this->assertFalse(validateJWT($expiredToken));
    }
}
