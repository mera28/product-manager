<?php
// public/index.php
session_start();

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../config/db.php';

try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Gagal mengambil data produk: " . $e->getMessage());
}

$status = $_GET['status'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Baju - Product Manager</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="header-actions">
            <h1>Katalog Koleksi Baju</h1>
            <a href="create.php" class="btn btn-primary">+ Tambah Baju Baru</a>
        </div>

        <?php if ($status === 'created'): ?>
            <div class="alert alert-success">Baju berhasil ditambahkan ke katalog!</div>
        <?php elseif ($status === 'updated'): ?>
            <div class="alert alert-success">Data baju berhasil diperbarui!</div>
        <?php elseif ($status === 'deleted'): ?>
            <div class="alert alert-danger">Baju berhasil dihapus dari katalog!</div>
        <?php endif; ?>

        <?php if (empty($products)): ?>
            <div class="empty-state">
                <p>Belum ada koleksi baju yang ditambahkan.</p>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if (!empty($product['image']) && file_exists(__DIR__ . '/uploads/' . $product['image'])): ?>
                                <img src="uploads/<?= htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>">
                            <?php else: ?>
                                <div class="no-image">Tidak ada foto</div>
                            <?php endif; ?>
                            <span class="category-badge"><?= htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <div class="product-price">Rp <?= number_format($product['price'], 0, ',', '.') ?></div>
                            <div class="product-stock">Stok: <strong><?= htmlspecialchars($product['stock'], ENT_QUOTES, 'UTF-8') ?> pcs</strong></div>
                            <div class="card-actions">
                                <a href="edit.php?id=<?= $product['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                
                                <form action="delete.php" method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus produk ini?')">
                                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>