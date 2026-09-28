# Product Manager - Web Aplikasi CRUD PHP Native

Aplikasi pengelolaan katalog produk (baju/pakaian) berbasis web yang dibuat menggunakan **PHP Native** dan **MySQL/MariaDB** dengan memperhatikan aspek keamanan aplikasi web (*Web Security Best Practices*).

## Fitur Keamanan (Web Security)

Aplikasi ini telah disesuaikan untuk mencegah berbagai celah keamanan umum pada web:

1. **Proteksi SQL Injection (SQLi)**
   - Seluruh kueri basis data menggunakan **PDO Prepared Statements** dengan *parameterized queries* pada operasi *Create*, *Read*, *Update*, dan *Delete*.

2. **Proteksi Cross-Site Scripting (XSS)**
   - Semua data yang ditampilkan ke pengguna dibungkus menggunakan fungsi `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` untuk mencegah eksekusi skrip jahat HTML/JavaScript.

3. **Proteksi Cross-Site Request Forgery (CSRF)**
   - Pengubahan dan penghapusan data menggunakan metode **HTTP POST**.
   - Diterapkan pembuatan **CSRF Token** berbasis *Session* yang divalidasi menggunakan `hash_equals()` pada form `create.php`, `edit.php`, dan `delete.php`.

4. **Validasi Input & Keamanan Upload File**
   - Filter tipe data angka (`FILTER_VALIDATE_FLOAT`, `FILTER_VALIDATE_INT`).
   - Pembatasan ekstensi file gambar (`jpg`, `jpeg`, `png`, `webp`).
   - Penamaan ulang file upload secara acak (`time() + uniqid()`) untuk menghindari konflik dan eksekusi file berbahaya.

## Teknologi yang Digunakan

- **Bahasa Pemrograman**: PHP 8.x (Native)
- **Database**: MySQL / MariaDB (Driver PDO)
- **Frontend**: HTML5, CSS3 (Custom Styling)
- **Server**: Apache (XAMPP / Laragon / Local Environment)

## Struktur Direktori Project

```
product-manager/
├── config/
│   └── db.php           # Koneksi database menggunakan PDO
├── public/
│   ├── assets/
│   │   └── style.css    # File styling CSS utama
│   ├── uploads/         # Folder penyimpanan gambar produk
│   ├── create.php       # Halaman tambah produk baru + CSRF Protection
│   ├── delete.php       # Handler proses hapus produk + CSRF Protection
│   ├── edit.php         # Halaman edit produk + CSRF Protection
│   └── index.php        # Halaman utama katalog produk
└── README.md            # Dokumentasi proyek
```

## Panduan Instalasi & Jalankan

### 1. Persiapan Database

Buat database baru di MySQL/phpMyAdmin dengan nama `product_manager` (atau sesuaikan), lalu jalankan perintah SQL berikut:

```sql
CREATE DATABASE IF NOT EXISTS product_manager;
USE product_manager;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) DEFAULT 'Pakaian',
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 2. Konfigurasi Koneksi Database

Buka file `config/db.php` 

```php
$host = 'localhost';
$db   = 'product_manager';
$user = 'root'; 
$pass = '';     
```

### 3. Jalankan Aplikasi

1. Pindahkan folder proyek ke dalam direktori server lokal 
   - **XAMPP**: `C:/xampp/htdocs/product-manager`
   - **Laragon**: `C:/laragon/www/product-manager`
2. Buka browser dan akses alamat berikut:
   `http://localhost/product-manager/public/index.php`

## Alur Penggunaan

1. **Melihat Katalog (`index.php`)**: Menampilkan seluruh data produk, stok, harga, serta opsi tindakan.
2. **Tambah Produk (`create.php`)**: Mengisi form nama, kategori, harga, stok, dan upload foto produk.
3. **Edit Produk (`edit.php`)**: Memperbarui informasi atau mengganti foto produk yang sudah ada.
4. **Hapus Produk (`delete.php`)**: Menghapus produk secara aman menggunakan metode `POST` beserta token validasi CSRF.

## Cara Pengujian Proteksi Keamanan

### Menguji Proteksi CSRF
1. Buka halaman katalog (`index.php`).
2. Klik kanan tombol **Hapus** pada salah satu produk -> pilih **Inspect**.
3. Ubah isi `value` dari elemen `<input type="hidden" name="csrf" value="...">` menjadi sembarang teks acak (misal: `value="palsu123"`).
4. Tekan tombol **Hapus**.
5. Sistem akan memblokir akses dan menampilkan pesan: **`Akses ditolak: Token CSRF tidak valid!`** dengan status HTTP 403.