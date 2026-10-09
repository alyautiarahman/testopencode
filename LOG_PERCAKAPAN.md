# Log Percakapan — Setup Laravel 13 & Aplikasi To-Do List

| | |
|---|---|
| **Tanggal** | 9 Oktober 2026 |
| **Repo** | https://github.com/alyautiarahman/testopencode |
| **Lokasi proyek** | `C:\xampp\htdocs\testopencode` |
| **User** | Alya (`Alya <alyalove@arieldaud.com>`) |

---

## Daftar Isi

1. [Ringkasan Permintaan](#1-ringkasan-permintaan)
2. [Sesi 1 — Setup Laravel 13](#2-sesi-1--setup-laravel-13)
3. [Sesi 2 — Menjalankan Development Server](#3-sesi-2--menjalankan-development-server)
4. [Sesi 3 — Membangun Aplikasi To-Do List](#4-sesi-3--membangun-aplikasi-to-do-list)
5. [Riwayat Commit](#5-riwayat-commit)
6. [Masalah yang Dihadapi & Solusi](#6-masalah-yang-dihadapi--solusi)
7. [Hasil Pengujian](#7-hasil-pengujian)
8. [Cara Menjalankan](#8-cara-menjalankan)
9. [Struktur File](#9-struktur-file)

---

## 1. Ringkasan Permintaan

Tiga permintaan berurutan dari user:

| # | Permintaan | Status |
|---|---|---|
| 1 | Setup Laravel 13 di `c:/xampp/htdocs/testopencode` | ✅ Selesai |
| 2 | Jalankan `php artisan serve` | ✅ Selesai |
| 3 | Buat aplikasi to-do list modern; buatkan log percakapan dalam format `.md`; push ke GitHub setiap ada perubahan; beri keterangan untuk setiap commit | ✅ Selesai |

---

## 2. Sesi 1 — Setup Laravel 13

### 2.1 Kondisi awal

- Folder `C:\xampp\htdocs\testopencode` sudah ada dan **kosong**.
- `php` (8.5.10) dan `composer` (2.10.3) sudah terpasang di PATH.

### 2.2 Yang dilakukan

1. **Membuat proyek**

   ```bash
   composer create-project laravel/laravel "C:\xampp\htdocs\testopencode" --prefer-dist --no-interaction
   ```

   Hasil: **Laravel 13.11.0** (`laravel/framework` v13.35.0), dependensi dipasang dari lock file.

2. **Mengaktifkan ekstensi `pdo_sqlite`**
   Migrasi default Laravel gagal karena driver SQLite tidak ditemukan:

   ```
   WARN could not find driver (Connection: sqlite, Database: .../database/database.sqlite)
   ```

   Ekstensi `sqlite3` aktif, tetapi `pdo_sqlite` masih dikomentari di `C:\xampp\php\php.ini` (baris 935):

   ```diff
   - ;extension=pdo_sqlite
   + extension=pdo_sqlite
   ```

3. **Menjalankan migrasi**

   ```
   0001_01_01_000000_create_users_table .. DONE
   0001_01_01_000001_create_cache_table .. DONE
   0001_01_01_000002_create_jobs_table  .. DONE
   ```

4. **Verifikasi** — `php artisan about` menunjukkan Laravel 13.35.0 / PHP 8.5.10, dan halaman welcome merespons **HTTP 200**.

### 2.3 Konfigurasi `.env`

```
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite
SESSION_DRIVER=database
CACHE_STORE=database
```

> **Catatan akses via XAMPP:** DocumentRoot Apache menunjuk ke `htdocs`, jadi untuk membuka lewat Apache gunakan `http://localhost/testopencode/public`, bukan `http://localhost/testopencode`.

---

## 3. Sesi 2 — Menjalankan Development Server

User meminta: *"jalankan php artisan serve"*.

```bash
php artisan serve --port=8000
```

Dijalankan di background (shell `sh_1203345260013ixQyTUl5H66tt`), diverifikasi mengembalikan **HTTP 200** di `http://127.0.0.1:8000`.

> Server perlu dijalankan ulang beberapa kali selama sesi karena prosesnya mati saat sesi sebelumnya ditutup.

---

## 4. Sesi 3 — Membangun Aplikasi To-Do List

### 4.1 Persiapan Git/GitHub

Pemeriksaan awal menemukan:

- `git` **tidak ada di PATH**, padahal terpasang di `C:\Program Files\Git\cmd\git.exe` → ditambahkan manual ke `PATH` untuk setiap perintah.
- `gh` (GitHub CLI) tidak terpasang.
- Repo sudah terinisialisasi dengan remote `origin → https://github.com/alyautiarahman/testopencode.git`, branch `main`, dan **credential helper `manager` sudah menyimpan kredensial GitHub** → push bisa dilakukan tanpa autentikasi ulang.
- Identitas git sudah terkonfigurasi: `Alya <alyalove@arieldaud.com>`.
- Node v24.21.0 / npm 11.19.0 tersedia untuk build aset.

### 4.2 Fitur aplikasi

| Fitur | Keterangan |
|---|---|
| Tambah task | Quick-add + detail opsional (prioritas, jatuh tempo, catatan) |
| Tandai selesai | Checkbox melingkar dengan animasi pop; `completed_at` dicatat |
| Edit task | Modal AJAX (`fetch` + validasi 422 dari server), tanpa reload penuh kecuali setelah sukses |
| Hapus task | Konfirmasi 2 langkah inline (Ya / Batal) |
| Filter | Semua / Aktif / Selesai — lewat query string `?filter=` |
| Pencarian | Debounce 350 ms, mencari judul **dan** deskripsi |
| Pengurutan | Terbaru, Jatuh tempo, Prioritas, Judul A–Z |
| Statistik | Bar progres gradien + total, aktif, selesai, terlambat |
| Label jatuh tempo | Hari ini, Besok, `D MMM`, atau "Terlambat N hr" + badge Overdue |
| Bersihkan yang selesai | Hapus semua task berstatus selesai sekaligus |
| Dark mode | Toggle manual, disimpan di `localStorage`, anti-FOUC |
| Toast notifikasi | Pesan flash dari session, auto-hide 3.2 detik |
| Empty state | 4 varian: kosong, tidak ada hasil, filter aktif, filter selesai |
| Responsif | Layout satu kolom, aksi task muncul saat hover di layar lebar |
| Aksesibilitas | `aria-pressed`, `aria-current`, `aria-modal`, `role=progressbar`, `aria-live`, fokus keyboard, tombol Escape menutup modal |

### 4.3 Keputusan teknis

| Keputusan | Alasan |
|---|---|
| **Server-side rendering** (Blade) untuk list | Filter/pencarian/pengurutan lewat query string → sederhana, SEO-friendly, tanpa API layer |
| **Alpine.js** hanya untuk interaksi | Dark mode, modal, debounce, konfirmasi — tanpa framework berat |
| **Tailwind CSS v4** via Vite | Sudah jadi default skeleton Laravel 13; ditambah `@custom-variant dark` untuk strategi class |
| **Modal edit via `fetch`** | Validasi error 422 ditampilkan inline di modal tanpa kehilangan input user |
| Form create/toggle/delete tetap **POST biasa** | Lebih andal (progressive enhancement), CSRF otomatis |
| Route `DELETE tasks/completed` didefinisikan **sebelum** resource | Agar tidak tertangkap parameter `{task}` pada route destroy |

### 4.4 Perubahan file

| Jenis | File |
|---|---|
| Baru | `database/migrations/2026_10_09_000001_create_tasks_table.php` |
| Baru | `app/Models/Task.php` |
| Baru | `database/factories/TaskFactory.php` |
| Baru | `app/Http/Controllers/TaskController.php` |
| Baru | `resources/js/{app,theme,toast,task-editor}.js` |
| Baru | `resources/views/components/{layout,priority-badge,task-item}.blade.php` |
| Baru | `resources/views/tasks/index.blade.php` |
| Baru | `tests/Feature/TaskTest.php` |
| Diubah | `routes/web.php`, `resources/css/app.css`, `database/seeders/DatabaseSeeder.php`, `tests/Feature/ExampleTest.php`, `package.json`, `C:\xampp\php\php.ini` |

### 4.5 Skema database `tasks`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint | PK |
| `title` | string(255) | Wajib |
| `description` | text, nullable | Catatan opsional |
| `priority` | string(8), diindeks | `low` / `medium` / `high`, default `medium` |
| `due_date` | date, diindeks | Jatuh tempo |
| `is_completed` | boolean, diindeks | Status selesai |
| `completed_at` | timestamp, nullable | Waktu diselesaikan |
| `created_at` / `updated_at` | timestamp | |

---

## 5. Riwayat Commit

Setiap perubahan di-commit dan **di-push ke GitHub** satu per satu.

| # | Hash | Waktu | Subject | Keterangan |
|---|---|---|---|---|
| 1 | `27434e0` | 19:38 | `feat(database): tambah tabel tasks beserta model, factory, dan seeder` | Migrasi tabel `tasks` (judul, deskripsi, prioritas, jatuh tempo, status). Model `Task` berisi konstanta prioritas, scope `active`/`completed`/`search`, dan helper `isOverdue()`/`isDueToday()`. `TaskFactory` + `DatabaseSeeder` mengisi 12 task contoh (7 sudah selesai). |
| 2 | `92506bc` | 19:39 | `feat(tasks): tambah TaskController dan rute CRUD` | `index()` dengan filter, pencarian, pengurutan, dan statistik progres; `store()`, `update()`, `toggle()`, `destroy()`, `clearCompleted()`. Resource route dibatasi hanya `store`/`update`/`destroy`. |
| 3 | `3988a1e` | 19:41 | `chore(frontend): tambah Alpine.js sebagai dependensi` | `alpinejs@^3.17.4` untuk interaksi UI ringan (dark mode, modal, debounce pencarian). |
| 4 | `8b97040` | 19:52 | `feat(frontend): siapkan Tailwind dark mode, animasi, dan modul Alpine` | Variant `dark` berbasis class, keyframes `fade-up`/`pop`/`slide-down`, utilitas `.bg-dots`, serta modul JS `theme.js`, `toast.js`, `task-editor.js`. |
| 5 | `a99ab95` | 19:52 | `feat(ui): bangun antarmuka to-do list modern dengan Blade + Alpine` | Layout + latar gradien, badge prioritas, baris task, dan halaman `tasks/index` lengkap dengan kartu progres, quick-add, toolbar filter, empty state, dan modal edit. |
| 6 | `67c81fd` | 19:53 | `fix(model): task jatuh tempo hari ini tidak lagi dianggap terlambat` | Cast `date` selalu bernilai `00:00` sehingga `isPast()` selalu `true` di hari yang sama → memunculkan "Terlambat 0 hr". Diperbaiki menjadi perbandingan tanggal (`due_date < today()`). |
| 7 | `3ae2b3d` | 19:53 | `test(tambahkan feature test untuk alur CRUD, filter, dan pencarian)` | 16 feature test (43 assertions). `ExampleTest` diberi `RefreshDatabase` karena kini memakai database in-memory. |
| 8 | `8723b82` | 19:55 | `docs(log): tambah log percakapan sesi setup dan pengembangan` | Menambahkan dokumen `LOG_PERCAKAPAN.md` ini — catatan sesi, riwayat commit, masalah & solusi, serta hasil pengujian. |

> Commit pertama pada repo (`92e0a07 "coba"`) sudah ada sebelum sesi ini dimulai.

---

## 6. Masalah yang Dihadapi & Solusi

| # | Masalah | Solusi |
|---|---|---|
| 1 | Migrasi gagal: `could not find driver (Connection: sqlite)` | `pdo_sqlite` dikomentari di `php.ini` → diaktifkan |
| 2 | Seeder error: `Call to a member function format() on null` | `fake()->optional(0.5)` mengembalikan `null` → diganti `fake()->boolean(50) ? ... : null` |
| 3 | `git` tidak dikenali di terminal | Terpasang di `C:\Program Files\Git\cmd` tapi tidak di PATH → ditambahkan manual ke `PATH` pada tiap perintah |
| 4 | Route `DELETE tasks/completed` bentrok dengan `DELETE tasks/{task}` | Rute khusus dipindah **sebelum** `Route::resource` |
| 5 | Error 500: `Undefined variable $loop` di `task-item.blade.php` | `$loop` tidak tersedia di dalam Blade component (scope terisolasi) → index dilewat eksplisit lewat `:index="$loop->index"` |
| 6 | `x-collapse` tidak berfungsi | Plugin `@alpinejs/collapse` tidak terpasang → diganti `x-transition` bawaan Alpine |
| 7 | **Bug UI:** task jatuh tempo hari ini menampilkan "Terlambat 0 hr" | `isPast()` pada cast `date` (timestamp `00:00`) selalu `true` → dibandingkan per tanggal: `due_date->lt(today())`. Ditutup dengan test regresi. |
| 8 | Debounce `x-model` membuat nilai DOM tertinggal saat user langsung mengganti urutan | Pindah ke `x-on:input.debounce` sehingga nilai DOM selalu terkini saat form disubmit |
| 9 | `Invoke-WebRequest` gagal padahal server merespons | Dialihkan ke `curl.exe` untuk pengujian HTTP |
| 10 | 2 dari 15 test gagal | `ExampleTest` tanpa `RefreshDatabase` (tabel tidak ada di `:memory:`) dan asersi "Bersihkan yang selesai" butuh task berstatus selesai → keduanya diperbaiki |

---

## 7. Hasil Pengujian

### 7.1 PHPUnit

```
PHPUnit 12.5.38
OK (16 tests, 43 assertions)
```

Cakupan `tests/Feature/TaskTest.php`:

| Test | Yang diverifikasi |
|---|---|
| `test_home_page_renders_with_tasks` | Halaman render dan menampilkan task |
| `test_home_page_handles_empty_state` | Empty state saat belum ada task |
| `test_user_can_create_a_task` | Create tersimpan ke database |
| `test_task_title_is_required` | Validasi judul wajib |
| `test_priority_must_be_valid` | Validasi prioritas harus `low/medium/high` |
| `test_user_can_toggle_completion` | Toggle on/off + `completed_at` terisi & direset |
| `test_user_can_update_a_task` | Update judul, prioritas, jatuh tempo |
| `test_user_can_delete_a_task` | Record terhapus |
| `test_clear_completed_only_removes_finished_tasks` | Hanya task selesai yang dihapus |
| `test_index_filters_by_status` | Filter aktif/selesai/nilai tak valid |
| `test_index_searches_tasks` | Pencarian cocok & tidak cocok |
| `test_priority_sort_puts_high_first` | Task prioritas `high` muncul lebih dulu |
| `test_task_due_today_is_not_marked_overdue` | **Regresi** untuk bug "Terlambat 0 hr" |
| `test_edit_modal_is_present_on_the_page` | Modal edit & tombol bersihkan ada |

### 7.2 Smoke test HTTP (server nyata)

| Alur | Hasil |
|---|---|
| `GET /` | 200 — 12 task tampil |
| `POST /tasks` (CSRF + session) | 302 — task muncul di list |
| `POST /tasks/{id}/toggle` | 302 — `aria-pressed` berubah ke `true` |
| `POST /tasks/{id}` + `_method=PUT` | 302 — judul & prioritas terupdate |
| `POST /tasks/{id}` + `_method=DELETE` | 302 — task hilang |
| `GET /?filter=active` | 200 — 5 task |
| `GET /?filter=completed` | 200 — 7 task |
| `GET /?q=dokumentasi` | 200 — 1 task |
| `GET /?sort=priority`, `/?sort=due` | 200 |
| `GET /build/assets/app-*.css` | 200 `text/css` (68.794 B) |
| `GET /build/assets/app-*.js` | 200 `application/javascript` (55.903 B) |

Data percobaan dari smoke test dibersihkan sehingga kembali ke 12 task contoh.

---

## 8. Cara Menjalankan

```bash
cd C:\xampp\htdocs\testopencode

# 1. Build aset (sekali setelah clone / saat CSS/JS berubah)
npm install
npm run build

# 2. Jalankan server
php artisan serve          # http://127.0.0.1:8000

# Via Apache XAMPP: http://localhost/testopencode/public

# 3. Uji
php artisan test

# 4. Isi ulang data contoh
php artisan migrate:fresh --seed
```

> `public/build` ada di `.gitignore`, jadi setiap clone baru wajib menjalankan `npm install && npm run build`.

---

## 9. Struktur File

```
testopencode/
├── app/
│   ├── Http/Controllers/
│   │   └── TaskController.php      # index, store, update, toggle, destroy, clearCompleted
│   └── Models/
│       └── Task.php                # cast, scope, helper jatuh tempo
├── database/
│   ├── factories/TaskFactory.php
│   ├── migrations/2026_10_09_000001_create_tasks_table.php
│   └── seeders/DatabaseSeeder.php
├── resources/
│   ├── css/app.css                 # Tailwind v4 + dark variant + keyframes
│   ├── js/
│   │   ├── app.js                  # registrasi komponen Alpine
│   │   ├── theme.js                # dark mode + localStorage
│   │   ├── task-editor.js          # state modal edit (fetch, error 422)
│   │   └── toast.js                # pesan flash auto-hide
│   └── views/
│       ├── components/
│       │   ├── layout.blade.php    # <head>, latar, toast
│       │   ├── priority-badge.blade.php
│       │   └── task-item.blade.php # baris task
│       └── tasks/index.blade.php   # halaman utama
├── routes/web.php
├── tests/Feature/TaskTest.php
└── LOG_PERCAKAPAN.md               # dokumen ini
```

---

## Rangkuman

- ✅ Laravel **13.11.0** terpasang dan terverifikasi di `C:\xampp\htdocs\testopencode`
- ✅ Aplikasi to-do list modern dibangun dengan Blade + Tailwind CSS v4 + Alpine.js
- ✅ **16 test lulus** (43 asersi), ditambah smoke test HTTP end-to-end
- ✅ **8 commit** dibuat selama sesi ini, masing-masing **di-push ke GitHub** dengan pesan commit yang menjelaskan perubahannya
- ✅ Log percakapan ini disimpan sebagai `LOG_PERCAKAPAN.md`
