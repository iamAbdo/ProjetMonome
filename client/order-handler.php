<?php
declare(strict_types=1);
require __DIR__ . '/../private/config.php';

header('Content-Type: application/json; charset=utf-8');

// ida mab3tch b post exit
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Méthode non autorisée.']);
    exit;
}

// gather info
$productId = (int)($_POST['product_id'] ?? 0);
$clientName = trim($_POST['client_name'] ?? '');
$clientPhone = trim($_POST['client_phone'] ?? '');
$clientAddress = trim($_POST['client_address'] ?? '');
$clientEmail = trim($_POST['client_email'] ?? '');
$qty = max(1, (int)($_POST['qty'] ?? 1));

// verfiy infor
if ($productId <= 0 || $clientName === '' || $clientPhone === '' || $clientAddress === '') {
    http_response_code(422);
    echo json_encode(['message' => 'Merci de compléter toutes les informations obligatoires.']);
    exit;
}

// verifier produit kayn
$productStmt = $connection->prepare('SELECT id, name FROM products WHERE id = ?');

if (!$productStmt) {
    http_response_code(500);
    echo json_encode(['message' => 'Erreur serveur interne.']);
    exit;
}

$productStmt->bind_param('i', $productId);
$productStmt->execute();
$product = $productStmt->get_result()?->fetch_assoc();

if (!$product) {
    http_response_code(404);
    echo json_encode(['message' => 'Produit introuvable.']);
    exit;
}

$connection->begin_transaction();

try {
    $orderStmt = $connection->prepare('INSERT INTO orders (client_name, client_phone, client_address, client_email, product_id, qty) VALUES (?, ?, ?, ?, ?, ?)');

    if (!$orderStmt) {
        throw new RuntimeException('Préparation de la commande échouée : ' . $connection->error);
    }

    $orderStmt->bind_param('ssssii', $clientName, $clientPhone, $clientAddress, $clientEmail, $productId, $qty);

    if (!$orderStmt->execute()) {
        throw new RuntimeException('Exécution de la commande échouée : ' . $orderStmt->error);
    }

    $orderId = $connection->insert_id;

    $connection->commit();

    echo json_encode([
        'message' => 'Merci ' . $clientName . ', votre commande est enregistrée !',
        'order_id' => $orderId
    ]);
} catch (Throwable $exception) {
    $connection->rollback();
    http_response_code(500);
    echo json_encode(['message' => 'Impossible d’enregistrer la commande.']);
}

