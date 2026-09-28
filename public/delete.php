<?php
// public/delete.php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method Not Allowed");
}

$userToken    = $_POST['csrf'] ?? '';
$sessionToken = $_SESSION['csrf'] ?? '';

if (!hash_equals($sessionToken, $userToken)) {
    http_response_code(403);
    exit("Akses ditolak: Token CSRF tidak valid!");
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id) {
    try {
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch();

        if ($product) {
            if (!empty($product['image'])) {
                $filePath = __DIR__ . '/uploads/' . $product['image'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $deleteStmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
            $deleteStmt->execute(['id' => $id]);
        }
    } catch (PDOException $e) {
        die("Gagal menghapus produk: " . $e->getMessage());
    }
}

header("Location: index.php?status=deleted");
exit;