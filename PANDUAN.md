# Panduan Lengkap: SPK Seleksi Penerimaan Karyawan Baru (AHP & TOPSIS)

Sistem Pendukung Keputusan untuk seleksi penerimaan karyawan baru menggunakan metode **Analytical Hierarchy Process (AHP)** dan **Technique for Order Preference by Similarity to Ideal Solution (TOPSIS)** berbasis Laravel.

---

## Daftar Isi

1. [Persyaratan Sistem](#1-persyaratan-sistem)
2. [Instalasi & Setup](#2-instalasi--setup)
3. [Menjalankan Aplikasi](#3-menjalankan-aplikasi)
4. [Akun Default](#4-akun-default)
5. [Alur Penggunaan Aplikasi](#5-alur-penggunaan-aplikasi)
   - [Langkah 1: Login](#langkah-1-login)
   - [Langkah 2: Kelola Data Kriteria](#langkah-2-kelola-data-kriteria)
   - [Langkah 3: Buat Periode Seleksi](#langkah-3-buat-periode-seleksi)
   - [Langkah 4: Input Data Pelamar](#langkah-4-input-data-pelamar)
   - [Langkah 5: Penilaian Pelamar](#langkah-5-penilaian-pelamar)
   - [Langkah 6: Perhitungan AHP](#langkah-6-perhitungan-ahp)
   - [Langkah 7: Perhitungan TOPSIS](#langkah-7-perhitungan-topsis)
   - [Langkah 8: Lihat Hasil Perangkingan](#langkah-8-lihat-hasil-perangkingan)
   - [Langkah 9: Cetak Laporan](#langkah-9-cetak-laporan)
   - [Langkah 10: Buat Pengumuman](#langkah-10-buat-pengumuman)
6. [Struktur Menu Aplikasi](#6-struktur-menu-aplikasi)
7. [Penjelasan Metode](#7-penjelasan-metode)
8. [Struktur Database](#8-struktur-database)
9. [Struktur File Proyek](#9-struktur-file-proyek)
10. [Troubleshooting](#10-troubleshooting)

---

## 1. Persyaratan Sistem

| Komponen      | Versi Minimum            |
|---------------|--------------------------|
| PHP           | 8.3 atau lebih baru      |
| Composer      | 2.x                      |
| MySQL/MariaDB | 8.0 / 10.4              |
| Node.js       | 18+ (opsional, untuk Vite) |
| Web Browser   | Chrome / Firefox / Edge  |

Pastikan juga ekstensi PHP berikut aktif:
- `pdo_mysql`
- `mbstring`
- `openssl`
- `tokenizer`
- `xml`
- `ctype`
- `json`
- `bcmath`

---

## 2. Instalasi & Setup

### 2.1 Clone / Siapkan Proyek

Jika belum memiliki proyek, pastikan folder proyek sudah tersedia di komputer Anda.

### 2.2 Install Dependensi PHP

Buka terminal di folder proyek, jalankan:

```bash
composer install
```

### 2.3 Konfigurasi Environment

Salin file `.env.example` menjadi `.env` (jika belum ada):

```bash
cp .env.example .env
```

Edit file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spk_karyawan
DB_USERNAME=root
DB_PASSWORD=
```

> **Penting:** Ganti `DB_DATABASE` dengan nama database yang Anda inginkan.

### 2.4 Generate Application Key

```bash
php artisan key:generate
```

### 2.5 Buat Database

Buat database baru di MySQL. Anda bisa menggunakan phpMyAdmin, HeidiSQL, atau command line:

```sql
CREATE DATABASE spk_karyawan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2.6 Jalankan Migrasi Database

Perintah ini akan membuat semua tabel yang diperlukan:

```bash
php artisan migrate
```

Tabel yang akan dibuat:
| No | Tabel                  | Fungsi                                       |
|----|------------------------|----------------------------------------------|
| 1  | `users`                | Data pengguna (Admin & Direktur)              |
| 2  | `selection_periods`    | Periode seleksi karyawan                      |
| 3  | `criteria`             | Kriteria penilaian (C1, C2, dst.)             |
| 4  | `sub_criteria`         | Sub-kriteria dari masing-masing kriteria      |
| 5  | `applicants`           | Data pelamar / calon karyawan                 |
| 6  | `pairwise_comparisons` | Matriks perbandingan berpasangan (AHP)        |
| 7  | `criteria_weights`     | Bobot kriteria hasil perhitungan AHP          |
| 8  | `evaluations`          | Nilai/skor pelamar per kriteria               |
| 9  | `selection_results`    | Hasil perangkingan TOPSIS                     |
| 10 | `announcements`        | Pengumuman hasil seleksi                      |

### 2.7 Jalankan Seeder (Data Awal)

Perintah ini akan mengisi data awal: akun pengguna, 7 kriteria, dan sub-kriteria:

```bash
php artisan db:seed
```

> **Catatan:** Jika ingin reset semua data dan mulai ulang:
> ```bash
> php artisan migrate:fresh --seed
> ```

---

## 3. Menjalankan Aplikasi

Jalankan server development Laravel:

```bash
php artisan serve
```

Aplikasi akan berjalan di: **http://localhost:8000**

Buka URL tersebut di browser. Anda akan diarahkan ke halaman login.

---

## 4. Akun Default

Setelah menjalankan seeder, tersedia 2 akun berikut:

| Role      | Email              | Password   | Hak Akses                                   |
|-----------|--------------------|------------|----------------------------------------------|
| Admin     | admin@spk.com      | password   | Semua fitur (CRUD, perhitungan, laporan)     |
| Direktur  | direktur@spk.com   | password   | Dashboard, laporan seleksi, pengumuman       |

> **Rekomendasi:** Segera ubah password setelah login pertama kali di lingkungan produksi.

---

## 5. Alur Penggunaan Aplikasi

Berikut adalah alur lengkap penggunaan sistem dari awal hingga menghasilkan keputusan:

```
Login → Kelola Kriteria → Buat Periode → Input Pelamar → Penilaian → AHP → TOPSIS → Hasil → Laporan → Pengumuman
```

### Langkah 1: Login

1. Buka `http://localhost:8000/login`
2. Masukkan email dan password
3. Klik tombol **Login**
4. Anda akan diarahkan ke **Dashboard**

Dashboard menampilkan ringkasan:
- Total periode seleksi
- Total pelamar
- Jumlah kriteria aktif
- Total karyawan yang lulus seleksi
- Tabel periode terbaru dan hasil seleksi terbaru

---

### Langkah 2: Kelola Data Kriteria

**Menu:** Sidebar > **Data Kriteria**

Secara default, sistem sudah memiliki 7 kriteria dari jurnal:

| Kode | Kriteria                 | Kepentingan | Tipe    |
|------|--------------------------|-------------|---------|
| C1   | Pendidikan Terakhir      | 9           | Benefit |
| C2   | Usia                     | 8           | Benefit |
| C3   | IPK                      | 6           | Benefit |
| C4   | Kemampuan Bahasa Asing   | 5           | Benefit |
| C5   | Wawancara                | 4           | Benefit |
| C6   | Pengalaman Kerja         | 3           | Benefit |
| C7   | Psikotest                | 2           | Benefit |

**Operasi yang tersedia:**

#### a. Tambah Kriteria Baru
1. Klik tombol **Tambah Kriteria**
2. Isi form:
   - **Kode**: Kode unik (contoh: C8)
   - **Nama**: Nama kriteria
   - **Tipe**: `Benefit` (semakin tinggi semakin baik) atau `Cost` (semakin rendah semakin baik)
   - **Kepentingan**: Nilai 1-9 (menentukan prioritas di AHP, semakin besar semakin penting)
   - **Deskripsi**: Opsional
3. Klik **Simpan**

#### b. Kelola Sub-Kriteria
Setiap kriteria memiliki sub-kriteria untuk mempermudah penilaian:

1. Di halaman Data Kriteria, klik link **Sub-kriteria (n)** pada kriteria yang diinginkan
2. Panel sub-kriteria akan terbuka
3. Isi form: **Nama**, **Nilai** (1-10), **Deskripsi** (opsional)
4. Klik tombol **+** untuk menyimpan

Contoh sub-kriteria C1 (Pendidikan Terakhir):
| Nama     | Nilai |
|----------|-------|
| SMA/SMK  | 1     |
| D3       | 2     |
| S1       | 3     |
| S2       | 4     |
| S3       | 5     |

#### c. Aktifkan/Nonaktifkan Kriteria
Klik ikon toggle pada kolom **Aksi** untuk mengaktifkan atau menonaktifkan kriteria. Kriteria nonaktif tidak akan digunakan dalam perhitungan.

#### d. Edit & Hapus Kriteria
Gunakan tombol edit (pensil) atau hapus (tong sampah) pada kolom Aksi.

---

### Langkah 3: Buat Periode Seleksi

**Menu:** Sidebar > **Periode Seleksi**

Periode seleksi merupakan "sesi" rekrutmen. Setiap kali perusahaan membuka lowongan, buat periode baru.

1. Klik **Tambah Periode**
2. Isi form:
   - **Nama Periode**: Contoh: "Seleksi Karyawan Batch 1 - 2026"
   - **Posisi**: Contoh: "Staff Administrasi"
   - **Tanggal Mulai**: Tanggal pembukaan seleksi
   - **Tanggal Selesai**: Tanggal penutupan seleksi
   - **Deskripsi**: Keterangan tambahan (opsional)
3. Klik **Simpan**

**Status Periode:**
| Status      | Keterangan                                    |
|-------------|-----------------------------------------------|
| `Draft`     | Baru dibuat, belum aktif                      |
| `Dibuka`    | Sedang menerima pelamar                       |
| `Ditutup`   | Tidak menerima pelamar baru, proses seleksi   |
| `Selesai`   | Seleksi telah selesai                         |

Ubah status melalui halaman **Edit Periode**.

---

### Langkah 4: Input Data Pelamar

**Menu:** Sidebar > **Data Pelamar**

1. Pilih **Periode Seleksi** dari dropdown filter (opsional, untuk menyaring tampilan)
2. Klik **Tambah Pelamar**
3. Isi form:
   - **Periode**: Pilih periode seleksi
   - **Nama Lengkap**: Nama pelamar
   - **Email**: Alamat email
   - **Telepon**: Nomor telepon
   - **Jenis Kelamin**: Laki-laki / Perempuan
   - **Tanggal Lahir**: Format tanggal
   - **Pendidikan Terakhir**: SMA/SMK, D3, S1, S2, S3
   - **Jurusan**: Bidang studi
   - **IPK**: Indeks Prestasi Kumulatif (0.00 - 4.00)
   - **Usia**: Dalam tahun (17-60)
   - **Alamat**: Alamat lengkap
4. Klik **Simpan**
5. Ulangi untuk semua pelamar

> **Tips:** Anda bisa memfilter pelamar berdasarkan periode menggunakan dropdown di atas tabel.

---

### Langkah 5: Penilaian Pelamar

**Menu:** Sidebar > **Penilaian Pelamar**

Di halaman ini, Anda memberikan skor/nilai untuk setiap pelamar terhadap setiap kriteria.

1. Pilih **Periode Seleksi** dari dropdown
2. Sistem akan menampilkan tabel dengan:
   - **Baris**: Nama pelamar
   - **Kolom**: Kriteria (C1, C2, C3, dst.)
   - **Sel**: Dropdown untuk memilih nilai
3. Untuk setiap pelamar, pilih nilai pada setiap kolom kriteria:
   - Jika kriteria memiliki sub-kriteria, dropdown akan menampilkan nama sub-kriteria beserta nilainya
   - Jika tidak ada sub-kriteria, dropdown menampilkan angka 1-5
4. Setelah semua terisi, klik **Simpan Penilaian**

**Contoh pengisian (berdasarkan jurnal):**

| Pelamar          | C1 | C2 | C3 | C4 | C5 | C6 | C7 |
|------------------|----|----|----|----|----|----|----|
| Aldefa Pratiwi   | 5  | 5  | 5  | 4  | 5  | 3  | 5  |
| Novela Andriyani | 5  | 4  | 5  | 3  | 4  | 3  | 3  |
| Mhd. Izzu Salam  | 3  | 5  | 3  | 3  | 4  | 4  | 2  |
| dst...           |    |    |    |    |    |    |    |

> **Penting:** Pastikan semua sel terisi sebelum menyimpan. Penilaian bisa diubah kapan saja dengan mengulang proses ini.

---

### Langkah 6: Perhitungan AHP

**Menu:** Sidebar > **Perhitungan AHP**

Metode AHP digunakan untuk menentukan **bobot** setiap kriteria berdasarkan tingkat kepentingannya.

1. Pilih **Periode Seleksi** dari dropdown
2. Klik tombol **Hitung AHP (Auto)**
   - Sistem akan otomatis membuat **matriks perbandingan berpasangan** berdasarkan nilai kepentingan yang sudah diatur di Data Kriteria
3. Sistem menampilkan hasil perhitungan:

#### a. Matriks Perbandingan Berpasangan
Tabel perbandingan antar kriteria. Diagonal bernilai 1 (kriteria dibandingkan dengan dirinya sendiri).

#### b. Matriks Ternormalisasi
Setiap nilai dibagi dengan jumlah kolomnya.

#### c. Bobot Prioritas
Rata-rata baris dari matriks ternormalisasi. Ini adalah **bobot** yang akan digunakan di TOPSIS.

Contoh hasil bobot:
| Kriteria                | Bobot  |
|-------------------------|--------|
| C1 - Pendidikan Terakhir| 0.2432 |
| C2 - Usia               | 0.2162 |
| C3 - IPK                | 0.1622 |
| C4 - Bahasa Asing       | 0.1351 |
| C5 - Wawancara          | 0.1081 |
| C6 - Pengalaman Kerja   | 0.0811 |
| C7 - Psikotest          | 0.0541 |

Bobot ditampilkan juga sebagai **progress bar** untuk visualisasi.

#### d. Uji Konsistensi
- **Lambda Max**: Eigenvalue maksimal
- **CI (Consistency Index)**: Indeks konsistensi
- **RI (Random Index)**: Indeks random berdasarkan jumlah kriteria
- **CR (Consistency Ratio)**: CI / RI

> **Aturan:** Jika **CR ≤ 0.1 (10%)**, maka penilaian dianggap **konsisten** dan bobot valid untuk digunakan. Jika CR > 0.1, perlu dilakukan perbaikan nilai kepentingan.

---

### Langkah 7: Perhitungan TOPSIS

**Menu:** Sidebar > **Perhitungan TOPSIS**

Metode TOPSIS digunakan untuk **merangking** pelamar berdasarkan nilai dan bobot kriteria.

1. Pilih **Periode Seleksi** dari dropdown
2. Isi **Jumlah yang Diterima** (contoh: 3 = tiga pelamar teratas akan berstatus "Lulus")
3. Klik **Hitung TOPSIS**
4. Sistem menampilkan detail perhitungan:

#### a. Matriks Keputusan
Tabel nilai mentah setiap pelamar untuk setiap kriteria.

#### b. Matriks Ternormalisasi
Setiap nilai dibagi dengan akar dari jumlah kuadrat kolom:

```
r_ij = x_ij / sqrt(Σ x_ij²)
```

#### c. Matriks Terbobot
Nilai ternormalisasi dikalikan bobot kriteria dari AHP:

```
v_ij = w_j × r_ij
```

#### d. Solusi Ideal
- **A+ (Ideal Positif)**: Nilai terbaik per kriteria (max untuk benefit, min untuk cost)
- **A- (Ideal Negatif)**: Nilai terburuk per kriteria (min untuk benefit, max untuk cost)

#### e. Jarak & Nilai Preferensi
- **D+**: Jarak ke solusi ideal positif
- **D-**: Jarak ke solusi ideal negatif
- **Nilai Preferensi**: D- / (D+ + D-)

Semakin tinggi nilai preferensi, semakin baik pelamar tersebut.

#### f. Hasil Perangkingan
Tabel akhir dengan ranking, nama pelamar, nilai preferensi, dan status (Lulus/Tidak Lulus).

---

### Langkah 8: Lihat Hasil Perangkingan

**Menu:** Sidebar > **Hasil Perangkingan**

Halaman ini menampilkan tabel ringkas hasil akhir seleksi:

| Rank | Nama Pelamar    | D+     | D-     | Nilai Preferensi | Status      |
|------|-----------------|--------|--------|------------------|-------------|
| 1    | Aldefa Pratiwi  | 0.0149 | 0.1316 | 0.8978           | Lulus       |
| 2    | Mhd. Rifqi      | 0.0362 | 0.1258 | 0.7765           | Lulus       |
| 3    | Dewa Abid       | 0.0397 | 0.1155 | 0.7444           | Lulus       |
| ...  | ...             | ...    | ...    | ...              | Tidak Lulus |

Pelamar dengan status **Lulus** ditandai dengan baris berwarna hijau.

---

### Langkah 9: Cetak Laporan

**Menu:** Sidebar > **Laporan Seleksi**

1. Pilih **Periode Seleksi** dari dropdown
2. Halaman akan menampilkan:
   - Informasi periode (nama, posisi, tanggal, pembuat)
   - Tabel bobot kriteria
   - Tabel nilai pelamar per kriteria beserta total terbobot
   - Tabel hasil akhir seleksi (rank, nama, nilai preferensi, status)
3. Klik tombol **Cetak Laporan**
4. Browser akan membuka tab baru dengan versi cetak
5. Dialog print akan otomatis muncul
6. Pilih printer atau "Save as PDF" lalu klik **Print**

Laporan cetak mencakup:
- Header perusahaan
- 3 tabel data lengkap
- Area tanda tangan (Yang Membuat Laporan & Mengetahui Direktur)
- Tanggal dan waktu cetak

---

### Langkah 10: Buat Pengumuman

**Menu:** Sidebar > **Pengumuman**

1. Klik **Tambah Pengumuman**
2. Isi form:
   - **Judul**: Judul pengumuman
   - **Periode**: Pilih periode terkait (opsional)
   - **Konten**: Isi pengumuman lengkap
   - **Publish**: Centang jika ingin langsung dipublikasikan
3. Klik **Simpan**

Pengumuman bisa diedit atau dihapus kapan saja melalui kolom Aksi di tabel pengumuman.

---

## 6. Struktur Menu Aplikasi

### Menu Admin (Role: Admin)

```
Menu Utama
├── Dashboard
│
├── Master Data
│   ├── Periode Seleksi
│   ├── Data Kriteria
│   └── Data Pelamar
│
├── Penilaian & Perhitungan
│   ├── Penilaian Pelamar
│   ├── Perhitungan AHP
│   ├── Perhitungan TOPSIS
│   └── Hasil Perangkingan
│
└── Laporan & Info
    ├── Laporan Seleksi
    └── Pengumuman
```

### Menu Direktur (Role: Direktur)

```
Menu Utama
├── Dashboard
│
└── Laporan & Info
    ├── Laporan Seleksi
    └── Pengumuman
```

---

## 7. Penjelasan Metode

### AHP (Analytical Hierarchy Process)

AHP dikembangkan oleh Thomas L. Saaty untuk menguraikan masalah multi-kriteria menjadi hierarki. Dalam sistem ini:

1. **Struktur Hierarki**: Tujuan (seleksi karyawan) → Kriteria (C1-C7) → Alternatif (pelamar)
2. **Matriks Perbandingan Berpasangan**: Setiap kriteria dibandingkan satu sama lain berdasarkan skala kepentingan 1-9
3. **Bobot Prioritas**: Dihitung dari normalisasi matriks perbandingan
4. **Uji Konsistensi**: CR harus ≤ 0.1 agar penilaian valid

**Skala Kepentingan AHP:**

| Nilai | Definisi                                              |
|-------|-------------------------------------------------------|
| 1     | Kedua elemen sama pentingnya                          |
| 3     | Elemen yang satu sedikit lebih penting                |
| 5     | Elemen yang satu lebih penting                        |
| 7     | Satu elemen jelas lebih mutlak penting                |
| 9     | Satu elemen mutlak penting                            |
| 2,4,6,8 | Nilai-nilai antara dua nilai pertimbangan berdekatan |

### TOPSIS (Technique for Order Preference by Similarity to Ideal Solution)

TOPSIS memilih alternatif yang memiliki jarak terdekat ke solusi ideal positif dan jarak terjauh dari solusi ideal negatif.

**Langkah-langkah:**
1. Membuat matriks keputusan ternormalisasi
2. Membuat matriks keputusan terbobot (menggunakan bobot dari AHP)
3. Menentukan solusi ideal positif (A+) dan negatif (A-)
4. Menghitung jarak setiap alternatif ke A+ dan A-
5. Menghitung nilai preferensi: `V_i = D_i- / (D_i+ + D_i-)`
6. Merangking alternatif berdasarkan nilai preferensi (tertinggi = terbaik)

---

## 8. Struktur Database

```
users
├── id, name, email, password, role (admin/direktur)

selection_periods
├── id, name, position, start_date, end_date, description, status, created_by

criteria
├── id, code, name, type (benefit/cost), importance, description, is_active

sub_criteria
├── id, criteria_id (FK), name, value, description

applicants
├── id, period_id (FK), name, email, phone, gender, birth_date, education, major, gpa, age, address

pairwise_comparisons
├── id, period_id (FK), criteria_row_id (FK), criteria_col_id (FK), value

criteria_weights
├── id, period_id (FK), criteria_id (FK), weight

evaluations
├── id, period_id (FK), applicant_id (FK), criteria_id (FK), score

selection_results
├── id, period_id (FK), applicant_id (FK), preference_value, positive_distance, negative_distance, rank, status

announcements
├── id, title, content, period_id (FK), is_published, published_at, created_by
```

---

## 9. Struktur File Proyek

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php           # Login & Logout
│   │   ├── DashboardController.php      # Halaman dashboard
│   │   ├── CriteriaController.php       # CRUD kriteria & sub-kriteria
│   │   ├── SelectionPeriodController.php # CRUD periode seleksi
│   │   ├── ApplicantController.php      # CRUD data pelamar
│   │   ├── EvaluationController.php     # Penilaian pelamar
│   │   ├── CalculationController.php    # AHP & TOPSIS
│   │   ├── ReportController.php         # Laporan & cetak
│   │   └── AnnouncementController.php   # Pengumuman
│   └── Middleware/
│       └── CheckRole.php                # Middleware cek role
├── Models/
│   ├── User.php
│   ├── SelectionPeriod.php
│   ├── Criteria.php
│   ├── SubCriteria.php
│   ├── Applicant.php
│   ├── PairwiseComparison.php
│   ├── CriteriaWeight.php
│   ├── Evaluation.php
│   ├── SelectionResult.php
│   └── Announcement.php
└── Services/
    ├── AhpService.php                   # Logika perhitungan AHP
    └── TopsisService.php                # Logika perhitungan TOPSIS

database/
├── migrations/                          # 12 file migrasi
└── seeders/
    └── DatabaseSeeder.php               # Data awal (user, kriteria, sub-kriteria)

resources/views/BE/
├── layouts/
│   ├── main.blade.php                   # Layout utama
│   ├── css.blade.php                    # CSS imports
│   ├── script.blade.php                 # JavaScript imports
│   ├── menu.blade.php                   # Sidebar menu
│   ├── header.blade.php                 # Header bar
│   └── footer.blade.php                # Footer
└── pages/
    ├── auth/login.blade.php             # Halaman login
    ├── dashboard.blade.php              # Dashboard
    ├── criteria/                         # CRUD kriteria
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   └── edit.blade.php
    ├── periods/                          # CRUD periode
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   ├── edit.blade.php
    │   └── show.blade.php
    ├── applicants/                       # CRUD pelamar
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   └── edit.blade.php
    ├── evaluations/
    │   └── index.blade.php              # Matriks penilaian
    ├── calculations/
    │   ├── ahp.blade.php                # Perhitungan AHP
    │   ├── topsis.blade.php             # Perhitungan TOPSIS
    │   └── results.blade.php            # Hasil perangkingan
    ├── reports/
    │   ├── index.blade.php              # Laporan seleksi
    │   └── print.blade.php              # Versi cetak
    └── announcements/                   # CRUD pengumuman
        ├── index.blade.php
        ├── create.blade.php
        └── edit.blade.php

routes/
└── web.php                              # Semua route aplikasi
```

---

## 10. Troubleshooting

### Masalah Umum

| Masalah | Solusi |
|---------|--------|
| Halaman blank / error 500 | Jalankan `php artisan config:clear && php artisan cache:clear` |
| Tabel tidak ditemukan | Jalankan `php artisan migrate` |
| Login gagal | Pastikan seeder sudah dijalankan: `php artisan db:seed` |
| CR > 0.1 (tidak konsisten) | Sesuaikan nilai kepentingan kriteria agar lebih konsisten |
| Perhitungan TOPSIS error | Pastikan AHP sudah dihitung terlebih dahulu untuk periode tersebut |
| Penilaian kosong | Isi semua nilai pelamar di halaman Penilaian Pelamar |
| DataTables tidak muncul | Refresh halaman (Ctrl+F5), pastikan koneksi internet aktif |

### Perintah Artisan Berguna

```bash
# Hapus semua cache
php artisan optimize:clear

# Reset database dan isi ulang data awal
php artisan migrate:fresh --seed

# Cek daftar route
php artisan route:list

# Jalankan server di port tertentu
php artisan serve --port=8080
```

### Kontak Dukungan

Jika menemukan bug atau masalah, periksa:
1. File log di `storage/logs/laravel.log`
2. Buka browser DevTools (F12) untuk melihat error JavaScript
3. Pastikan semua persyaratan sistem terpenuhi

---

**Selamat menggunakan Sistem Pendukung Keputusan Seleksi Penerimaan Karyawan Baru!**
