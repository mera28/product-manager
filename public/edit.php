<?php
// public/edit.php
require_once __DIR__ . '/../config/db.php';

$errors = [];
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $product = $stmt->fetch();

    if (!$product) {
        header("Location: index.php");
        exit;
    }
} catch (PDOException $e) {
    die("Gagal mengambil data: " . $e->getMessage());
}

$name     = $product['name'];
$category = $product['category'];
$price    = $product['price'];
$stock    = $product['stock'];
$oldImage = $product['image'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price    = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $stock    = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
    $imageName = $oldImage; 

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
        $fileTmpPath   = $_FILES['image']['tmp_name'];
        $fileName      = $_FILES['image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = time() . '_' . uniqid() . '.' . $fileExtension;
            $uploadFileDir = __DIR__ . '/uploads/';

            if (move_uploaded_file($fileTmpPath, $uploadFileDir . $newFileName)) {
                if ($oldImage && file_exists($uploadFileDir . $oldImage)) {
                    unlink($uploadFileDir . $oldImage);
                }
                $imageName = $newFileName;
            } else {
                $errors['image'] = "Gagal mengunggah gambar baru.";
            }
        } else {
            $errors['image'] = "Format gambar harus JPG, JPEG, PNG, atau WEBP.";
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                "UPDATE products SET name = :name, category = :category, price = :price, stock = :stock, image = :image WHERE id = :id"
            );
            $stmt->execute([
                'name'     => $name,
                'category' => $category ?: 'Pakaian',
                'price'    => $price,
                'stock'    => $stock,
                'image'    => $imageName,
                'id'       => $id
            ]);

            header("Location: index.php?status=updated");
            exit;
        } catch (PDOException $e) {
            $errors['general'] = "Gagal memperbarui data: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Baju - Product Manager</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Data Baju</h1>
        <a href="index.php" class="btn btn-secondary" style="margin-bottom: 20px;">← Batal & Kembali</a>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
        <?php endif; ?>

        <form action="edit.php?id=<?= htmlspecialchars($id) ?>" method="POST" enctype="multipart/form-data" class="form-card">
            <div class="form-group">
                <label for="name">Nama Baju / Produk</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
                <?php if (isset($errors['name'])): ?>
                    <small class="text-danger"><?= $errors['name'] ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="category">Kategori / Ukuran</label>
                <input type="text" id="category" name="category" value="<?= htmlspecialchars($category) ?>">
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
                <label for="image">Ganti Foto Baju (Opsional)</label>
                <?php if ($oldImage && file_exists(__DIR__ . '/uploads/' . $oldImage)): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="uploads/<?= htmlspecialchars($oldImage) ?>" alt="Foto Lama" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                        <span style="font-size: 12px; color: #64748b; display: block;">Foto saat ini</span>
                    </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" accept="image/*">
                <?php if (isset($errors['image'])): ?>
                    <small class="text-danger"><?= $errors['image'] ?></small>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>
</body>
</html>