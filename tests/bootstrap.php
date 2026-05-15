<?php

declare(strict_types=1);

require_once __DIR__ . '/../homeworkout/JWT/jwt_helper.php';
require_once __DIR__ . '/../homeworkout/tenant_helper.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
