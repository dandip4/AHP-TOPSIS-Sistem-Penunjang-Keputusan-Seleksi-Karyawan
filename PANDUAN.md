# Panduan Lengkap: SPK Seleksi Penerimaan Karyawan (AHP, KMKK & TOPSIS)

Sistem Pendukung Keputusan (SPK) untuk seleksi penerimaan karyawan menggunakan:

- **AHP** (Analytical Hierarchy Process) — bobot kriteria per periode seleksi  
- **KMKK** (Keputusan Multi Kriteria Kelompok / Group Decision Making) — penilaian dari **beberapa evaluator** (mis. HRD, Manager, Direktur), lalu **agregasi** menjadi satu matriks keputusan  
- **TOPSIS** — perangkingan alternatif pelamar berbasis matriks **nilai yang sudah diagregasi kelompok** dan bobot AHP  

Aplikasi dibangun dengan **Laravel 13**, PHP **8.3+**, biasanya database **MySQL/MariaDB**, antarmuka **Able Pro / Bootstrap**.

---

## Daftar Isi

1. [Gambaran Besar Arsitektur](#1-gambaran-besar-arsitektur)
2. [Persyaratan Sistem](#2-persyaratan-sistem)
3. [Instalasi & Setup](#3-instalasi--setup)
4. [Menjalankan Aplikasi](#4-menjalankan-aplikasi)
5. [Peran Pengguna & Hak Akses](#5-peran-pengguna--hak-akses)
6. [Akun Demo (Seeder)](#6-akun-demo-seeder)
7. [Alur End-to-End: Dari Awal Sampai Keputusan](#7-alur-end-to-end-dari-awal-sampai-keputusan)
   - [Ringkasan diagram alir](#71-ringkasan-diagram-alir)
   - [Langkah detail](#72-langkah-detail)
8. [Penjelasan Modul Per Halaman](#8-penjelasan-modul-per-halaman)
9. [KMKK: Multi Evaluator & Agregasi](#9-kmkk-multi-evaluator--agregasi)
10. [Penjelasan Metode (AHP, OWA/Yager, TOPSIS)](#10-penjelasan-metode-ahp-owayager-toposis)
11. [Struktur Database](#11-struktur-database)
12. [Struktur File Proyek (Ringkas)](#12-struktur-file-proyek-ringkas)
13. [Perintah Berguna & Troubleshooting](#13-perintah-berguna--troubleshooting)

---

## 1. Gambaran Besar Arsitektur

1. **Data master**: kriteria (+ sub-kriteria), periode seleksi, pelamar, **evaluator** (nama/label dan opsional tautan ke akun login).  
2. **Masukan kelompok**: setiap evaluator mengisi **skor sama** untuk setiap pasangan (pelamar × kriteria), disimpan di tabel **`evaluations`** dengan kolom **`evaluator_id`**.  
3. **Agregasi KMKK**: Administrator menjalankan proses pada halaman **Evaluasi Kelompok (KMKK)** untuk menghitung **nilai satu per sel** (per periode × pelamar × kriteria). Hasil disimpan di **`aggregated_evaluations`**. Metode yang tersedia: **rata-rata** atau **OWA bahasa Yager** dengan kuantifikasi \(Q(r) = r^{\alpha}\).  
4. **AHP**: menghasilkan **bobot kriteria per periode** (`criteria_weights`) dari matriks perbandingan berpasangan (`pairwise_comparisons`).  
5. **TOPSIS**: membaca **hanya matriks agregat** (`aggregated_evaluations`) + bobot AHP (`criteria_weights`), lalu menghitung jarak ideal, nilai preferensi, ranking, dan status lulus untuk kuota tertentu.  
6. **Laporan & cetak** menampilkan ringkas bobot AHP, nilai pelamar (**agregasi**), rincian penilaian mentah per evaluator (sampel), dan hasil perangkingan.

---

## 2. Persyaratan Sistem

| Komponen      | Versi minimum            |
|---------------|---------------------------|
| PHP           | 8.3 atau lebih baru       |
| Composer      | 2.x                       |
| MySQL/MariaDB | Disarankan 8.x / 10.4+   |
| Web browser   | Chrome / Firefox / Edge   |

Pastikan ekstensi PHP antara lain: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`.

---

## 3. Instalasi & Setup

### 3.1 Dependensi PHP

```bash
composer install
```

### 3.2 File `.env`

Salin `.env.example` menjadi `.env` (jika belum), lalu atur database dan `APP_URL`:

```env
APP_NAME="SPK Seleksi Karyawan"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_anda
DB_USERNAME=root
DB_PASSWORD=
```

### 3.3 Application Key

```bash
php artisan key:generate
```

### 3.4 Buat Database MySQL

```sql
CREATE DATABASE nama_database_anda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3.5 Migrasi

```bash
php artisan migrate
```

Migrasi mencakup antara lain: pengguna; periode; kriteria; sub-kriteria; pelamar; AHP/TOPSIS (**evaluators**, **aggregated_evaluations**, **evaluations** dengan `evaluator_id`); hasil seleksi; pengumuman.

### 3.6 Data Awal (Seeder)

```bash
php artisan db:seed
```

Untuk menghapus semua data dan mengisi ulang dari nol:

```bash
php artisan migrate:fresh --seed
```

---

## 4. Menjalankan Aplikasi

```bash
php artisan serve
```

Buka **`http://localhost:8000`**. Anda akan diarahkan ke halaman login jika belum masuk.

---

## 5. Peran Pengguna & Hak Akses

| Peran (`users.role`) | Keterangan | Akses menu utama |
|---------------------|-------------|-------------------|
| **admin** | Pengelola penuh | Master data (periode, kriteria, pelamar, evaluator), penilaian (pilih evaluator), KMKK (agregasi), AHP/TOPSIS/hasil, laporan, pengumuman |
| **evaluator** | Anggota kelompok penilai (HRD/Manager, dll.) | Dashboard, Penilaian (hanya skor evaluator sendiri), KMKK (lihat tanpa tombol rebuild), laporan seleksi |
| **direktur** | Pimpinan; bisa juga evaluator jika ada **rekaman evaluator** tertaut ke akun tersebut | Dashboard, penilaian (jika terhubung sebagai evaluator), KMKK (lihat), laporan |

Middleware **`role`** (alias dari `CheckRole`) membatasi rute seperti CRUD master, **AHP**, **TOPSIS**, **pengumuman**, CRUD **evaluator**, serta **POST `/kmkk/rebuild`** — hanya **admin**.

---

## 6. Akun Demo (Seeder)

Setelah **`php artisan db:seed`** atau **`migrate:fresh --seed`**, sekurunya contoh akun mengikuti pola berikut (**password untuk semua: `password`**):

| Peran     | Email            | Catatan |
|-----------|-----------------|--------|
| Admin     | admin@spk.com   | Mengelola semua konfigurasi dan agregasi KMKK |
| Direktur  | direktur@spk.com| Bisa akses laporan/KMKK; juga **terhubung** sebagai salah satu evaluator (lihat seed) |
| Evaluator | hrd@spk.com      | Mengisi penilaian sebagai evaluator HRD |
| Evaluator | manager@spk.com  | Mengisi penilaian sebagai evaluator Manager |

> Di produksi **wajib** mengganti password dan menyusun evaluators + user sesuai kebijakan perusahaan.

---

## 7. Alur End-to-End: Dari Awal Sampai Keputusan

### 7.1 Ringkasan diagram alir

Urutan yang **disarankan** dan selaras dengan kode aplikasi:

```text
Login
  → [Admin] Data Kriteria & Sub-kriteria
  → [Admin] Periode Seleksi
  → [Admin] Data Pelamar (per periode)
  → [Admin] Data Evaluator (nama, label, tautan user opsional)
  → Penilaian Pelamar (setiap evaluator mengisi matriks sendiri; admin bisa ganti dropdown evaluator)
  → [Admin] Evaluasi Kelompok (KMKK): hitung matriks agregasi (rata-rata atau OWA)
  → [Admin] Perhitungan AHP (bobot per periode)
  → [Admin] Perhitungan TOPSIS (pakai matriks agregat + bobot AHP; set kuota lulus)
  → Hasil Perangkingan, Laporan, Cetak
  → [Admin] Pengumuman (opsional)
```

**Catatan penting:** TOPSIS **tidak** langsung membaca baris `evaluations` per evaluator; ia memakai **`aggregated_evaluations`**. Jika agregasi belum lengkap atau belum dihitung, sistem menampilkan pesan agar menyelesaikan langkah KMKK.

### 7.2 Langkah detail

#### A. Login

1. Buka `/login`.  
2. Masuk dengan salah satu akun yang valid.  
3. Dashboard menampilkan ringkasan (periode, pelamar, kriteria, hasil lulus, grafik, dll.).

#### B. [Admin] Data Kriteria

**Menu:** Data Kriteria  

- Tambah/edit kriteria: kode, nama, tipe **benefit/cost**, **kepentingan** (untuk pembuatan matriks AHP otomatis), status aktif.  
- Sub-kriteria: dipakai sebagai opsi nilai pada dropdown penilaian (nilai numerik 1–5 sesuai desain seed).  
- Kriteria nonaktif tidak ikut dalam perhitungan.

#### C. [Admin] Periode Seleksi

**Menu:** Periode Seleksi  

- Buat periode: nama, posisi, tanggal, status (draft / dibuka / ditutup / selesai), pembuat.  
- Kolom tambahan di basis data: **`aggregation_method`**, **`owa_alpha`**, **`aggregation_computed_at`** diisi saat agregasi KMKK dijalankan.

#### D. [Admin] Data Pelamar

**Menu:** Data Pelamar  

- Setiap pelamar **terikat** ke satu `period_id`.  
- Lengkapi biodata sesuai form (email, pendidikan, IPK, usia, dll.).

#### E. [Admin] Data Evaluator (KMKK)

**Menu:** Data Evaluator (KMKK)  

- Definisikan evaluator: nama, kode unik (opsional), label peran (mis. "HRD"), urutan tampil, aktif/nonaktif.  
- **Tautkan ke user** (opsional): satu user maksimal satu evaluator; memungkinkan login evaluator untuk mengisi penilaian sendiri.  
- Evaluator yang masih punya baris di `evaluations` **tidak boleh dihapus** (pencegahan integritas data).

#### F. Penilaian Pelamar (multi-evaluator)

**Menu:** Penilaian Pelamar  

- **Admin:** pilih **periode** dan **evaluator yang sedang aktif** (dropdown), lalu isi matriks skor; simpan. Ulangi untuk evaluator lain sampai semua anggota kelompok selesai.  
- **Evaluator (login):** hanya melihat dan menyimpan skor **untuk dirinya sendiri** (tanpa memilih evaluator lain).  
- Skor per sel disimpan dengan **unik** `(period_id, applicant_id, criteria_id, evaluator_id)`.  
- Setiap kali menyimpan penilaian untuk suatu periode, **matriks agregat** untuk periode itu **dikosongkan** (harus dihitung ulang di KMKK).

#### G. [Admin] Evaluasi Kelompok (KMKK)

**Menu:** Evaluasi Kelompok (KMKK) — rute: `/kmkk`  

1. Pilih **periode**.  
2. Lihat tabel **penilaian mentah** (per pelamar, kriteria, evaluator).  
3. Pilih metode agregasi:  
   - **Rata-rata aritmetik** — rata-rata skor semua evaluator yang ada pada sel tersebut.  
   - **OWA (`owa_most`)** — OWA bahasa Yager dengan \(Q(r)=r^{\alpha}\) (\(\alpha>1\)); parameter **α** bisa diisi (default konsisten dengan kolom di periode).  
4. Klik **Hitung matriks agregat**.  

Sistem mengharuskan **setiap kombinasi (pelamar × kriteria aktif)** memiliki **minimal satu** nilai evaluator. Jika ada sel kosong total, agregasi gagal dan pesan kesalahan ditampilkan.

#### H. [Admin] Perhitungan AHP

**Menu:** Perhitungan AHP  

1. Pilih periode.  
2. Jalankan hitungan (otomatis dari nilai kepentingan kriteria atau input matriks jika Anda extend UI manual).  
3. Perhatikan **CR** konsistensi: idealnya **≤ 0,1**.

#### I. [Admin] Perhitungan TOPSIS

**Menu:** Perhitungan TOPSIS  

1. Pastikan **KMKK** sudah menghasilkan matriks agregat lengkap untuk periode yang sama.  
2. Pastikan **AHP** sudah menghasilkan bobot untuk periode tersebut.  
3. Isi **jumlah yang diterima** (kuota "lulus") jika ingin membatasi secara berperingkat teratas `N`.  
4. Jalankan hitungan. Preferensi jarak dan ranking disimpan ke **`selection_results`**.

#### J. Hasil Perangkingan & Laporan

- **Hasil Perangkingan:** ringkas per periode.  
- **Laporan seleksi:** bobot kriteria; **nilai agregat** pelamar × kriteria; sampel penilaian mentah **per evaluator**; tabel ranking.  
- **Cetak:** versi print-friendly dari ringkasan utama.

#### K. [Admin] Pengumuman

Mengatur teks pengumuman terhubung dengan periode (opsional) dan publikasi.

---

## 8. Penjelasan Modul Per Halaman

| Modul | Fungsi |
|-------|--------|
| Dashboard | Statistik dan grafik; pintasan informasi |
| Periode seleksi | Sesi rekruut satu periode satu set pelamar dan penilaian |
| Data Kriteria | Definisi C1–Cn, sub-skala penilaian, benefit/cost |
| Data Pelamar | Kandidat per periode |
| Data evaluator | Pengambil keputusan kelompok (KMKK) dan taut ke user |
| Penilaian Pelamar | Matriks skor **per evaluator** |
| Evaluasi Kelompok (KMKK) | Agregasi skor evaluator → **`aggregated_evaluations`** |
| Perhitungan AHP | Bobot kriteria per periode |
| Perhitungan TOPSIS | Ranking dari matriks agregat + bobot |
| Hasil perangkingan | Ringkasan hasil tersimpan |
| Laporan / cetak | Dokumentasi keputusan |
| Pengumuman | Komunikasi hasil kepada pelamar/pekerja |

---

## 9. KMKK: Multi Evaluator & Agregasi

### Mengapa ada dua tabel (`evaluations` vs `aggregated_evaluations`)?

- **`evaluations`** = jejak lengkap pendapat individu evaluator (audit, transparansi, analisis perbedaan opini).  
- **`aggregated_evaluations`** = **satu nilai sintetik** per (periode, pelamar, kriteria) yang menjadi **satunya "matriks keputusan TOPSIS** agar konsisten dengan model hybrid **kelompok + TOPSIS klasik satu matriks**.

### Kapan harus menghitung ulang agregasi?

- Setelah **menyimpan** penilaian (per evaluator) untuk sebuah periode, agregasi lama untuk periode tersebut **dibatalkan secara logis** (dihapus dari database). Lakukan lagi **Hitung matriks agregat** di halaman **KMKK**.

### Rumus OWA sederhana (Yager linguistic quantifier)

Untuk sebuah sel dengan sekumpulan skor yang diurut naik \(s_{(1)}\le\dots\le s_{(n)}\) dan \(Q(r)=r^{\alpha},\ \alpha>1\):

\[ w_j = Q(j/n)-Q((j-1)/n),\quad\text{OWL value}=\sum_j w_j,s_{(j)} \]

Implementasi tepat ada di **`App\Services\GroupDecisionAggregator`**.

---

## 10. Penjelasan Metode (AHP, OWA/Yager, TOPSIS)

### AHP

Digunakan untuk **bobot kriteria**. Matriks perbandingan berpasangan bisa dihasilkan otomatis dari **field kepentingan** kriteria. Uji konsistensi **CR ≤ 0,1**.

### OWA dalam konteks kelompok (ringkas)

Digunakan hanya sebagai **aggregator** atas skor para evaluator per sel. Ini **bukan** pengganti TOPSIS.

### TOPSIS

- Input matriks: **aggregated_scores** \(\times\) kriteria bobot \(w_j\) dari AHP  
- Tahapan: normalisasi, pembobotan jarak Euclidean ke solusi ideal positif dan negatif, nilai preferensi, ranking.

Detail rumus bisa dilihat juga di **`AhpService`** dan **`TopsisService`**.

---

## 11. Struktur Database

Versi tinggi (kolom utama; lihat migrations untuk lengkapnya):

```
users                          — nama, email, password, role (varchar: admin/evaluator/direktur, dll.)

selection_periods              — nama, tanggal, status, pembuat ...
                               + aggregation_method, owa_alpha, aggregation_computed_at

evaluators                     — nama, code, role_label, user_id (nullable uniq), sort_order, is_active

criteria, sub_criteria         — struktur dan skala nilai penilaian

applicants                     — pelamar bound ke period_id

evaluations                    — period_id, applicant_id, criteria_id, evaluator_id, score
                               — UNIQUE (period_id, applicant_id, criteria_id, evaluator_id)

aggregated_evaluations         — period_id, applicant_id, criteria_id, aggregated_score,
                                 aggregation_method, evaluator_count_used
                               — UNIQUE (period_id, applicant_id, criteria_id)

pairwise_comparisons           — untuk AHP
criteria_weights               — bobot hasil AHP per periode
selection_results              — ranking TOPSIS, status lulus/gagal
announcements                  — pengumuman
```

---

## 12. Struktur File Proyek (Ringkas)

```
app/
  Http/Controllers/
    AuthController.php
    DashboardController.php
    CriteriaController.php, SelectionPeriodController.php, ApplicantController.php
    EvaluatorController.php              — CRUD evaluator
    EvaluationController.php              — Penilaian per evaluator (+ clear agregasi)
    KmkkGroupResultController.php        — Halaman KMKK + POST rebuild agregasi
    CalculationController.php             — AHP & TOPSIS
    ReportController.php
    AnnouncementController.php
  Http/Middleware/CheckRole.php
  Models/ ...
  Services/
    AhpService.php
    TopsisService.php                     — Baca aggregated_evaluations
    GroupDecisionAggregator.php           — Rata-rata & OWA, simpan aggregated_evaluations

database/migrations/...
database/seeders/DatabaseSeeder.php       — Pengguna demo, evaluator, penilaian 3 evaluator, rebuild agregasi

resources/views/BE/...
routes/web.php
```

---

## 13. Perintah Berguna & Troubleshooting

| Masalah | Tindakan yang disarankan |
|---------|---------------------------|
| TOPSIS: pesan kurang lengkap dari KMKK | Buka **KMKK** untuk periode yang sama dan pastikan setiap cel pelamar × kriteria punya minimal satu penilaian, lalu **Hitung matriks agregat** lagi |
| TOPSIS minta jalankan AHP dulu | Hitung **AHP** untuk periode tersebut sampai ada `criteria_weights` |
| Setelah menyimpan penilaian, ranking TOPSIS hilang/perlu konfirmasi | Normal: agregasi dikosongkan; jalankan lagi **rebuild agregasi** lalu jalankan lagi **TOPSIS** jika sudah Anda hitung ulang bobot/perlu update |
| MySQL gagal menjatuhkan constraint unik evaluations | Migrasi sudah menggunakan indeks pembantu untuk FK dan `Schema::withoutForeignKeyConstraints`; gunakan `migrate:fresh` hanya untuk dev |
| Forgot password dalam dev | Jalankan lagi `migrate:fresh --seed` atau reset manual di DB |
| Ikons / cache ganda | `php artisan optimize:clear`, hard refresh browser |

```bash
php artisan migrate:fresh --seed   # RESET total + data demo
php artisan route:list               # Daftar endpoint
php artisan serve --port=8080       # Jalankan pada port tertentu
```

Log aplikasi: `storage/logs/laravel.log`.

---

**Dokumen ini menjelaskan alur sistem secara keseluruhan dari setup lingkungan menuju keputusan seleksi bersama menggunakan metode hibrida AHP, agregasi kelompok KMKK, dan TOPSIS. Untuk penyusunan akademik Anda dapat menyitir modul matematika yang terkait dari kelas layanan pada namespace `App\Services`.**
