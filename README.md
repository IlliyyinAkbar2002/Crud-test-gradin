# Courier Management API

Laravel API untuk mengelola master data Kurir (Courier) — CRUD lengkap dengan pagination, pencarian, filter, dan validasi input.

## Persyaratan

- PHP 8.2+
- PostgreSQL 12+
- Composer

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Konfigurasi koneksi PostgreSQL di file `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Jalankan migrasi:

```bash
php artisan migrate
```

## Database — Tabel `couriers`

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| `id` | bigint | PK, auto-increment | |
| `name` | varchar(255) | NOT NULL | Nama kurir |
| `phone` | varchar(20) | NOT NULL, UNIQUE | Nomor HP (format Indonesia) |
| `email` | varchar(255) | NULLABLE, UNIQUE | Email |
| `address` | text | NULLABLE | Alamat |
| `vehicle_type` | varchar(20) | NOT NULL | `Motorcycle`, `Car`, `Truck` |
| `license_plate` | varchar(15) | NULLABLE | Nomor plat kendaraan |
| `level` | unsigned tinyint | NOT NULL, default 1, CHECK 1–5 | Level kurir |
| `is_active` | boolean | NOT NULL, default true | Status aktif |
| `created_at` | timestamp | | Tanggal didaftarkan |
| `updated_at` | timestamp | | |

**Index:** `name`, `level`

**CHECK constraint (PostgreSQL):** `level BETWEEN 1 AND 5`

---

## API Endpoints

Base URL: `/api/couriers`

### 1. List Couriers — `GET /api/couriers`

Mengembalikan daftar kurir dengan pagination.

#### Query Parameters

| Parameter | Tipe | Default | Keterangan |
|---|---|---|---|
| `search` | string | — | Cari kurir berdasarkan nama (pencarian parsial per kata) |
| `level` | string | — | Filter level, format koma. Contoh: `2,3` |
| `sort` | string | `name` | Kolom pengurutan: `name` atau `created_at` |
| `direction` | string | `asc` | Arah pengurutan: `asc` atau `desc` |
| `per_page` | integer | 15 | Jumlah item per halaman (1–100) |

#### Contoh Request

```bash
# Default — urutkan berdasarkan nama
GET /api/couriers

# Urutkan berdasarkan tanggal didaftarkan (terbaru duluan)
GET /api/couriers?sort=created_at&direction=desc

# Cari kurir yang namanya mengandung kata "budi" DAN "agung"
GET /api/couriers?search=budi+agung

# Filter hanya level 2 dan 3
GET /api/couriers?level=2,3

# Kombinasi
GET /api/couriers?search=budi&level=2,3&sort=created_at&direction=desc&per_page=10
```

#### Response (200)

```json
{
  "data": [
    {
      "id": 1,
      "name": "Agung Prasetyo",
      "phone": "081234567890",
      "email": "agung@example.com",
      "address": "Jl. Sudirman No. 5",
      "vehicle_type": "Motorcycle",
      "license_plate": "L 1234 AB",
      "level": 3,
      "is_active": true,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "links": { "...": "..." },
  "meta": {
    "current_page": 1,
    "total": 1,
    "per_page": 15,
    "...": "..."
  }
}
```

---

### 2. Show Courier — `GET /api/couriers/{id}`

Mengembalikan detail satu kurir.

#### Response (200)

```json
{
  "data": {
    "id": 1,
    "name": "Agung Prasetyo",
    "phone": "081234567890",
    "email": "agung@example.com",
    "address": "Jl. Sudirman No. 5",
    "vehicle_type": "Motorcycle",
    "license_plate": "L 1234 AB",
    "level": 3,
    "is_active": true,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

---

### 3. Create Courier — `POST /api/couriers`

#### Request Body

| Field | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `name` | string | ✅ | Maks 255 karakter |
| `phone` | string | ✅ | Format Indonesia: `08xxxxxxxxxx`, `+62...`, atau `62...`. Unik |
| `email` | string | ❌ | Format email valid. Unik |
| `address` | string | ❌ | Maks 1000 karakter |
| `vehicle_type` | string | ✅ | Salah satu: `Motorcycle`, `Car`, `Truck` |
| `license_plate` | string | ❌ | Maks 15 karakter |
| `level` | integer | ✅ | Antara 1–5 |
| `is_active` | boolean | ❌ | Default `true` |

#### Contoh Request

```json
{
  "name": "Eko Nugroho",
  "phone": "081234567890",
  "email": "eko@example.com",
  "address": "Jl. Merdeka No. 1",
  "vehicle_type": "Motorcycle",
  "license_plate": "L 1234 AB",
  "level": 3,
  "is_active": true
}
```

#### Response (201)

```json
{
  "data": {
    "id": 6,
    "name": "Eko Nugroho",
    "...": "..."
  }
}
```

#### Response Validasi Gagal (422)

```json
{
  "message": "The name field is required. (and 3 more errors)",
  "errors": {
    "name": ["The name field is required."],
    "phone": ["The phone field is required."],
    "vehicle_type": ["The vehicle type field is required."],
    "level": ["The level field is required."]
  }
}
```

---

### 4. Update Courier — `PUT /api/couriers/{id}`

Semua field yang sama seperti Create, wajib diisi penuh pada PUT. Pada PATCH, field yang tidak dikirim tidak akan divalidasi (rule `sometimes`).

#### Response (200)

Mengembalikan data kurir yang sudah di-update.

```json
{
  "data": {
    "id": 1,
    "name": "Budi Updated",
    "...": "..."
  }
}
```

---

### 5. Delete Courier — `DELETE /api/couriers/{id}`

#### Response (204)

Tanpa body content.

---

## Testing

### Setup Test Database

Buat database khusus testing di PostgreSQL:

```sql
CREATE DATABASE crud_test_gradin_testing;
```

Buat file `.env.testing`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=crud_test_gradin_testing
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### Menjalankan Test

```bash
# Semua test
php artisan test

# Hanya test Courier
php artisan test --filter=CourierTest
```

### Cakupan Test

File: `tests/Feature/CourierTest.php` — **13 test, 46 assertion**

| Kategori | Test | Keterangan |
|---|---|---|
| **Index** | `returns paginated couriers sorted by name by default` | Pagination aktif, urutan default berdasarkan nama (A→Z) |
| | `can sort by created_at descending` | Override sort ke `created_at` dengan direction `desc` |
| | `can search couriers by partial name match` | `?search=budi+agung` match "Budiono Hadi Agung" |
| | `can filter by level` | `?level=2,3` hanya mengembalikan kurir level 2 dan 3 |
| **Show** | `returns a single courier with all fields` | Mengembalikan semua field kurir |
| **Store** | `creates a courier and persists to database` | Data tersimpan ke database (`assertDatabaseHas`) |
| | `validates required fields on store` | Error 422 jika field wajib kosong |
| | `validates phone format on store` | Error 422 jika format HP tidak valid |
| | `validates level range on store` | Error 422 jika level di luar 1–5 |
| | `validates vehicle_type enum on store` | Error 422 jika vehicle_type bukan Motorcycle/Car/Truck |
| **Update** | `updates a courier and persists changes` | Data ter-update di database (`assertDatabaseHas`) |
| | `validates required fields on update` | Error 422 jika field wajib kosong |
| **Destroy** | `deletes a courier and removes from database` | Data hilang dari database (`assertDatabaseMissing`) |

### Arsitektur Test

- **Framework:** Pest PHP
- **Trait:** `RefreshDatabase` (migrasi ulang setiap test suite)
- **Factory:** `CourierFactory` — menghasilkan data valid secara otomatis

## Struktur Project (Courier)

```
app/
├── Http/
│   ├── Controllers/
│   │   └── CourierController.php        # CRUD logic
│   ├── Requests/
│   │   ├── CourierRequest.php           # Validasi store & update
│   │   └── IndexCourierRequest.php      # Validasi query index (search, sort, filter)
│   └── Resources/
│       └── CourierResource.php          # JSON response transformation
├── Models/
│   └── Courier.php                      # Model + scopeSearch, scopeOfLevels
database/
├── factories/
│   └── CourierFactory.php               # Test data factory
├── migrations/
│   └── 2026_11_23_111733_create_couriers_table.php
routes/
└── api.php                              # Route::apiResource('couriers', ...)
tests/
└── Feature/
    └── CourierTest.php                  # 13 feature tests
```
