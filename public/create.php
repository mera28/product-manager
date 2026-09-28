<?php
// public/create.php
require_once __DIR__ . '/../config/db.php';

$errors = [];
$name = '';
$category = '';
$price = '';
$stock = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price    = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $stock    = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
    $imageName = null;

    if (mb_strlen($name) < 3) {
        $errors['name'] = "Nama produk minimal 3 karakter.";
    }
    if ($price === false || $price <= 0) {
        $errors['price'] = "Harga harus lebih besar dari 0.";
    }
    if ($stock === false || $stock < 0) {
        $errors['stock'] = "Stok tidak boleh negatif.";
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName    = $_FILES['image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = time() . '_' . uniqid() . '.' . $fileExtension;
            $uploadFileDir = __DIR__ . '/uploads/';
            
            if(!is_dir($uploadFileDir)){
                mkdir($uploadFileDir, 0755, true);
            }
            
            $dest_path = $uploadFileDir . $newFileName;
            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $imageName = $newFileName;
            } else {
                $errors['image'] = "Gagal mengunggah gambar.";
            }
        } else {
            $errors['image'] = "Format gambar harus JPG, JPEG, PNG, atau WEBP.";
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO products (name, category, price, stock, image) VALUES (:name, :category, :price, :stock, :image)"
            );
            $stmt->execute([
                'name'     => $name,
                'category' => $category ?: 'Pakaian',
                'price'    => $price,
                'stock'    => $stock,
                'image'    => $imageName
            ]);

            header("Location: index.php?status=created");
            exit;
        } catch (PDOException $e) {
            $errors['general'] = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk Baru</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Tambah Baju / Produk Baru</h1>
        <a href="index.php" class="btn btn-secondary">← Kembali ke Katalog</a>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
        <?php endif; ?>

        <form action="create.php" method="POST" enctype="multipart/form-data" class="form-card">
            <div class="form-group">
                <label for="name">Nama Baju / Produk</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="Contoh: Kaos Oversize Black" required>
                <?php if (isset($errors['name'])): ?>
                    <small class="text-danger"><?= $errors['name'] ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="category">Kategori / Ukuran</label>
                <input type="text" id="category" name="category" value="<?= htmlspecialchars($category) ?>" placeholder="Contoh: Kaos, Kemeja, Outer">
            </div>

            <div class="form-group">
                <label for="price">Harga (Rp)</label>
                <input type="number" id="price" name="price" step="0.01" value="<?= htmlspecialchars($price) ?>" required>
            </div>

            <div class="form-group">
                <label for="stock">Stok</label>
                <input type="number" id="stock" name="stock" value="<?= htmlspecialchars($stock) ?>" required>
            </div>

            <div class="form-group">
                <label for="image">Foto Baju</label>
                <input type="file" id="image" name="image" accept="image/*">
                <?php if (isset($errors['image'])): ?>
                    <small class="text-danger"><?= $errors['image'] ?></small>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Produk</button>
        </form>
    </div>
</body>
</html>