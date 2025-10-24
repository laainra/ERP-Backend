

# 🏭 ERP Backend - 

Backend untuk sistem ERP (Enterprise Resource Planning) yang dibangun menggunakan **Laravel 9**.  
Menyediakan RESTful API untuk modul:
- 🔐 Authentication (Laravel Sanctum)
- 📦 Products
- 🧩 Production Plan
- 🧾 Production Order
- 🧱 Production Log
- 📊 Production Report
- 👥 User Management

---

## ⚙️ Tech Stack

- **Framework**: Laravel 9
- **Language**: PHP 8.0+
- **Database**: MySQL / MariaDB
- **Authentication**: Laravel Sanctum
- **CORS**: fruitcake/laravel-cors

---

## 📁 Folder Structure (Utama)

```

ERP-Backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── API/
│   │   ├── Middleware/
│   │   └── Kernel.php
│   ├── Models/
│   └── Providers/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── routes/
│   ├── api.php
│   └── web.php
├── .env
├── artisan
└── composer.json

````

---

## 🚀 Installation Guide

### 1️⃣ Clone Repository
```bash
git clone https://github.com/laainra/ERP-Backend.git
cd ERP-Backend
````

### 2️⃣ Install Dependencies

```bash
composer install
```

### 3️⃣ Setup Environment

Copy file `.env.example` ke `.env` dan sesuaikan konfigurasi berikut:

```bash
cp .env.example .env
```

Ubah variabel sesuai dengan environment kamu:

```env
APP_NAME="ERP System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erp_system
DB_USERNAME=root
DB_PASSWORD=

FRONTEND_URL=http://localhost:8080
```

Lalu jalankan:

```bash
php artisan key:generate
```

---

## 🧱 Database Setup

### Jalankan migrasi dan seeder:

```bash
php artisan migrate --seed
```

Seeder akan menambahkan data awal seperti user admin dan contoh produk.

---

## 🔐 Authentication (Sanctum)

Untuk API Authentication gunakan Laravel Sanctum:

* Login menghasilkan token bearer.
* Setiap request API (kecuali login/register) butuh Authorization header:

  ```
  Authorization: Bearer <token>
  ```

---

## 🌐 API Routes

Semua route berada di `routes/api.php`.

Contoh endpoint:

| Method | Endpoint                 | Deskripsi                   |
| ------ | ------------------------ | --------------------------- |
| POST   | `/api/login`             | Login user                  |
| GET    | `/api/products`          | Get all products            |
| POST   | `/api/products`          | Create new product          |
| GET    | `/api/production-plans`  | List production plans       |
| POST   | `/api/production-orders` | Create new production order |

---

## 🔄 CORS Setup

Gunakan package bawaan:

```bash
composer require fruitcake/laravel-cors
```

Tambahkan middleware di `app/Http/Kernel.php`:

```php
\App\Http\Middleware\HandleCors::class,
```

Atur `config/cors.php`:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => ['http://localhost:8080'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'supports_credentials' => true,
```

---

## 🧰 Development Command List

| Command                               | Deskripsi                               |
| ------------------------------------- | --------------------------------------- |
| `php artisan serve`                   | Jalankan backend                        |
| `php artisan migrate:fresh --seed`    | Reset dan isi ulang DB                  |
| `php artisan route:list`              | Lihat semua endpoint API                |
| `php artisan storage:link`            | Link storage ke public                  |
| `php artisan make:model Product -mcr` | Generate model + controller + migration |

---

## 🔗 API Connection (Frontend Integration)

Frontend (Vue.js) terhubung ke endpoint:

```
http://127.0.0.1:8000/api/
```

Gunakan axios base URL:

```js
axios.defaults.baseURL = "http://127.0.0.1:8000/api";
```

---

## 👨‍💻 Author

**ERP System by Laila Ainur rahma**
Built with ❤️ using Laravel 9.

---

## 🧩 License

This project is open-sourced under the [MIT license](https://opensource.org/licenses/MIT).

