<?php
header("Content-Type: application/json");
session_start();
require_once '../database.php';
require_once '../tenant_helper.php';

if (!isset($_SESSION['utente_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autenticato']);
    exit;
}

$ruoloNome = $_SESSION['ruolo_nome'] ?? 'utente';
if (!homeworkoutIsSuperAdmin($_SESSION['ruolo_id'] ?? null, $ruoloNome)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Accesso riservato al super admin']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'list') {
    try {
        $stmt = $pdo->query("SELECT t.id, t.nome, t.slug, t.stato, COUNT(u.id) AS utenti
                             FROM tenants t
                             LEFT JOIN utenti u ON u.tenant_id = t.id
                             GROUP BY t.id, t.nome, t.slug, t.stato
                             ORDER BY t.id DESC");

        echo json_encode(['success' => true, 'tenants' => $stmt->fetchAll()]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $nome = trim($data['nome'] ?? '');
    $slug = trim($data['slug'] ?? '');

    if ($nome === '') {
        echo json_encode(['success' => false, 'error' => 'Nome palestra obbligatorio']);
        exit;
    }

    if ($slug === '') {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $nome));
        $slug = trim($slug, '-');
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO tenants (nome, slug, stato) VALUES (:nome, :slug, 'active')");
        $stmt->execute(['nome' => $nome, 'slug' => $slug]);

        echo json_encode(['success' => true, 'tenant_id' => (int)$pdo->lastInsertId()]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Impossibile creare la palestra. Slug già usato?']);
    }
    exit;
}

if ($action === 'activate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $tenantId = (int)($data['tenant_id'] ?? 0);

    if ($tenantId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Tenant non valido']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, nome FROM tenants WHERE id = :tenant_id LIMIT 1");
    $stmt->execute(['tenant_id' => $tenantId]);
    $tenant = $stmt->fetch();

    if (!$tenant) {
        echo json_encode(['success' => false, 'error' => 'Palestra non trovata']);
        exit;
    }

    homeworkoutEnsureSessionStarted();
    $_SESSION['active_tenant_id'] = $tenantId;
    $_SESSION['tenant_id'] = $tenantId;
    $_SESSION['tenant_nome'] = $tenant['nome'];

    echo json_encode(['success' => true, 'tenant_id' => $tenantId, 'tenant_nome' => $tenant['nome']]);
    exit;
}

if ($action === 'assign_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $userId = (int)($data['user_id'] ?? 0);
    $tenantId = (int)($data['tenant_id'] ?? 0);

    if ($userId <= 0 || $tenantId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Dati non validi']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE utenti SET tenant_id = :tenant_id WHERE id = :user_id");
        $stmt->execute(['tenant_id' => $tenantId, 'user_id' => $userId]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Azione non supportata']);
