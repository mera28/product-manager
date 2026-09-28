<?php
// public/delete.php
require_once __DIR__ . '/../config/db.php';

$id = $_GET['id'] ?? null;

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