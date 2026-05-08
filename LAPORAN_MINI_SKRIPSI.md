
# MINI SKRIPSI

## SISTEM PENDUKUNG KEPUTUSAN SELEKSI PENERIMAAN KARYAWAN BERBASIS WEB MENGGUNAKAN INTEGRASI METODE ANALYTICAL HIERARCHY PROCESS (AHP), AGREGASI KELOMPOK MULTI-EVALUATOR (KMKK), DAN TECHNIQUE FOR ORDER OF PREFERENCE BY SIMILARITY TO IDEAL SOLUTION (TOPSIS)

**Disusun Oleh:**  
**Danadipa Nugraha SM**  
**(065123199)**

**PROGRAM STUDI ILMU KOMPUTER**  
**FAKULTAS MATEMATIKA DAN ILMU PENGETAHUAN ALAM**  
**UNIVERSITAS PAKUAN**  
**BOGOR**  
**2026**

---

## HALAMAN PENGESAHAN

| | |
|---|---|
| **Judul** | Sistem Pendukung Keputusan Seleksi Penerimaan Karyawan Berbasis Web Menggunakan Integrasi Metode Analytical Hierarchy Process (AHP), Agregasi Kelompok Multi-Evaluator (KMKK), dan Technique for Order of Preference by Similarity to Ideal Solution (TOPSIS) |
| **Nama** | Danadipa Nugraha |
| **NPM** | 065123199 |

**Mengesahkan,**

**Pembimbing Pendamping**  
Program Studi Ilmu Komputer  
FMIPA – UNPAK  

**Pembimbing Pendamping**  
Program Studi Ilmu Komputer  
FMIPA – UNPAK  

**(Dr. Eneng Tita Tosida., S.Tp., M.Si., M.Kom.)**  
**(Dr. Eneng Tita Tosida., S.Tp., M.Si., M.Kom.)**

---

**Mengetahui,**

**Ketua Program Studi Ilmu Komputer FMIPA – UNPAK**  
**(Arie Qur'ania, M.Kom)**

**Dekan FMIPA - UNPAK**  
**(Asep Denih, S.Kom., M.Sc., Ph.D.)**

---

## KATA PENGANTAR

Puji dan syukur penulis panjatkan ke hadirat Tuhan Yang Maha Esa atas rahmat dan karunia-Nya sehingga penulis dapat menyelesaikan mini skripsi yang berjudul *Sistem Pendukung Keputusan Seleksi Penerimaan Karyawan Berbasis Web Menggunakan Integrasi Metode Analytical Hierarchy Process (AHP), Agregasi Kelompok Multi-Evaluator (KMKK), dan Technique for Order of Preference by Similarity to Ideal Solution (TOPSIS)* dengan baik.

Mini skripsi ini disusun sebagai salah satu syarat untuk memenuhi tugas mata kuliah **Metodologi Penelitian** pada Program Studi Ilmu Komputer, Fakultas Matematika dan Ilmu Pengetahuan Alam, Universitas Pakuan. Penulis menyadari bahwa penyusunan naskah ini masih dapat diperdalam, baik secara metodologi maupun bukti pengujian lapangan.

Ucapan terima kasih penulis sampaikan kepada:

1. Dosen pengampu mata kuliah Metodologi Penelitian dan pembimbing akademik atas arahan serta koreksi yang membangun.  
2. Orang tua dan keluarga atas doa serta dukungan.  
3. Teman-teman di lingkungan kampus atas diskusi dan motivasi.

Akhir kata, penulis berharap karya ini bermanfaat bagi penulis khususnya dan pembaca pada umumnya sebagai dokumentasi akademik atas implementasi SPK rekruitment berbasis web.

**Bogor, 30 April 2026**  

**Danadipa Nugraha**  
**065123199**

---

## DAFTAR ISI

| | Hal |
|---|:---:|
| HALAMAN PENGESAHAN | i |
| KATA PENGANTAR | ii |
| DAFTAR ISI | iii |
| DAFTAR GAMBAR | iv |
| DAFTAR TABEL | v |
| BAB I PENDAHULUAN | 1 |
| &nbsp;&nbsp;1.1 Latar Belakang | 1 |
| &nbsp;&nbsp;1.2 Tujuan Penelitian | 2 |
| &nbsp;&nbsp;1.3 Ruang Lingkup | 2 |
| &nbsp;&nbsp;1.4 Manfaat Penelitian | 3 |
| BAB II TINJAUAN PUSTAKA | 4 |
| &nbsp;&nbsp;2.1 Landasan Teori | 4 |
| &nbsp;&nbsp;2.2 Penelitian Terdahulu | 8 |
| &nbsp;&nbsp;2.3 Tabel Perbandingan | 9 |
| BAB III METODOLOGI PENELITIAN | 10 |
| &nbsp;&nbsp;3.1 Software Development Life Cycle (SDLC) | 10 |
| BAB IV PERANCANGAN DAN IMPLEMENTASI | 14 |
| &nbsp;&nbsp;4.1 Perancangan | 14 |
| &nbsp;&nbsp;4.2 Implementasi | 18 |
| BAB V HASIL DAN PEMBAHASAN | 21 |
| &nbsp;&nbsp;5.1 Hasil | 21 |
| &nbsp;&nbsp;5.2 Pembahasan | 24 |
| &nbsp;&nbsp;5.3 Pengujian | 25 |
| BAB VI KESIMPULAN DAN SARAN | 28 |
| DAFTAR PUSTAKA | 29 |

---

## DAFTAR GAMBAR

| No. | Judul | Hal. |
|:---:|:---|:---:|
| Gambar 1. | Model Software Development Life Cycle – Waterfall | 10 |
| Gambar 2. | Arsitektur sistem (lapisan presentasi–aplikasi–data) | 15 |
| Gambar 3. | Alur keputusan end-to-end (kriteria → penilaian → KMKK → AHP → TOPSIS) | 16 |
| Gambar 4. | Contoh antarmuka modul penilaian multi-evaluator *(disisipkan tangkapan layar)* | 22 |
| Gambar 5. | Contoh halaman laporan seleksi *(disisipkan tangkapan layar)* | 23 |

*Catatan: nomor halaman disesuaikan setelah pengetikan final di pengolah kata.*

---

## DAFTAR TABEL

| No. | Judul | Hal. |
|:---:|:---|:---:|
| Tabel 1. | Ringkasan variabel data keputusan dan lokasi penyimpanan | 7 |
| Tabel 2. | Perbandingan penelitian terdahulu | 9 |
| Tabel 3. | Kebutuhan fungsional utama sistem | 11 |
| Tabel 4. | Pemetaan tahapan proses data keputusan (adaptasi KDD) | 12 |
| Tabel 5. | Modul dan rute aplikasi | 18 |
| Tabel 6. | Rangkuman uji struktural antarmuka | 25 |
| Tabel 7. | Rangkuman uji fungsionalitas jalur utama | 26 |
| Tabel 8. | Rangkuman uji konsistensi data / skenario demo | 27 |

---

# BAB I  
## PENDAHULUAN

### 1.1 Latar Belakang

Proses rekruitment sering mencakup **banyak kriteria penilaian** dan **partisipasi lebih dari satu pihak** (misalnya HRD, manajer, atau pimpinan). Tanpa dokumentasi sistematis, keputusan rentan tidak transparan, sulit diverifikasi, dan sulit dibandingkan lintas **periode seleksi**. *Sistem Pendukung Keputusan* (SPK) menawarkan kerangka struktur hirarkis dan perhitungan terukur atas data penilaian (Turban, Aronson, & Liang, 2005).

**Analytical Hierarchy Process (AHP)** dipakai untuk menurunkan **bobot relatif antar-kriteria** dari **matriks perbandingan berpasangan** (Saaty, 1980). Pada konteks rekruitment berkelompok, skor evaluator individual per kombinasi (pelamar, kriteria) perlu diagregasi menjadi **satu nilai konsensus per sel**. Pendekatan keputusan multi-kriteria kelompok sering menggunakan agregasi skor seperti **rata-rata** atau operator **Ordered Weighted Averaging (OWA)** milik **Yager** dengan kuantifikasi linguistik (Yager, 1988)—dalam dokumentasi aplikasi penyusun menyebut jalur tersebut sebagai komponen **KMKK** *(Keputusan Multi Kriteria Kelompok)* secara operasional.

Setelah bobot dan matriks agregat konsisten tersedia, **TOPSIS** dapat memeringkatkan alternatif (pelamar) berdasarkan kedekatan dari **titik ideal positif** dan **titik ideal negatif** dalam ruang keputusan tertimbang (Hwang & Yoon, 1981).

Dari sisi teknologi, kerangka **Laravel** mempermudah implementasi pola **Model–View–Controller (MVC)**, otentikasi, migrasi basis data, dan pemisahan akses berdasarkan **peran pengguna**. Integrasi tersebut relevan bagi mahasiswa Ilmu Komputer yang memadukan teoritis-metodologis rekayasa perangkat lunak dengan model keputusan.

### 1.2 Tujuan Penelitian

Tujuan penelitian ini dirumuskan secara bertahap sebagai berikut:

1. Merancang **alur keputusan** yang menghubungkan **penilaian multi-evaluator**, **agregasi kelompok (KMKK)**, **pembobotan AHP per periode**, dan **TOPSIS** dalam satu aplikasi bersambung.  
2. Mengimplementasikan **prototype sistem berbasis web** menggunakan **Laravel**, **database relasional**, dan antarmuka **Blade–Bootstrap**.  
3. Mendokumentasikan **hasil fungsional** dan **rangkaian pengujian** yang dapat direplikasi menggunakan data **demo** atau skenario pembimbing pada lingkungan lokal pengembangan.

### 1.3 Ruang Lingkup

Agar pekerjaan tetap tertata dalam kerangka **mini skripsi satu semester**, ditetapkan batasan berikut:

1. **Bidang aplikasi**: seleksi atau rekrutmen dengan **alternatif pelamar**, **bobot dinamis tiap periode**, dan pemetaan **kriteria aktif melalui relasi pivot** antar **periode** dan **kriteria** pada basis data aplikasi penyusun (`PANDUAN.md`, dokumentasi repo).  
2. **Konfigurasi agregasi KMKK**: penyedia dua mode operasional, yaitu **rata-rata aritmetik** dan **OWA Yager** dengan parameter α > 1 (*sesuai implementasi servis `GroupDecisionAggregator` pada proyek*).  
3. **Peran pengguna**: minimal **administrator**, **direktur**, dan **evaluator** dengan pembatasan rute **middleware** `role` pada implementasi web (`routes/web.php`).  
4. **Evaluasi**: difokuskan pada **uji fungsionalitas** dan **konsistensi alur** dengan skenario data contoh (seeder), bukan uji signifikansi statistik perbandingan metode di lapangan.  
5. **Keamanan produksi** (HTTPS, hardening server, kebijakan organisasi) **di luar** ruang lingkup naskah mini skripsi ini.

### 1.4 Manfaat Penelitian

Secara **teoritis**, penelitian memperkuat pemahaman integrasi **MCDM** (Multi-Criteria Decision Making) dan **GDM** (Group Decision Making) dalam satu pipeline perangkat lunak. Secara **praktis**, prototipe dapat menjadi **referensi implementasi** bagi institusi yang membutuhkan dokumentasi terstruktur dalam seleksi karyawan. Bagi **penulis**, proyek menjadi latihan metodologi penelitian terapan di bidang rekayasa perangkat lunak keputusan.

---

# BAB II  
## TINJAUAN PUSTAKA

### 2.1 Landasan Teori

#### 2.1.1 Sistem Pendukung Keputusan (SPK)

SPK adalah sistem berbasis komputer yang membantu pengambil keputusan memanfaatkan data dan model, tanpa menggantikan **wewenang keputusan akhir** manusia (Turban dkk., 2005). Komponen umum meliputi **basis data**, **model analisis**, dan **antarmuka pengguna**.

#### 2.1.2 Analytical Hierarchy Process (AHP)

AHP menguraikan masalah ke dalam **hierarki** dan memanfaatkan **perbandingan berpasangan** antar elemen pada level yang sama. Hasil utamanya berupa **vektor prioritas (bobot)** yang dapat **dinormalisasi**. **Consistency Ratio (CR)** dipakai untuk menilai kekonsistenan penilai (Saaty, 1980). Pada aplikasi penyusun, bobot hasil AHP per periode disimpan untuk **input TOPSIS**.

#### 2.1.3 Keputusan Multi Kriteria Kelompok dan OWA Yager

**Keputusan kelompok** menggabungkan preferensi atau skor dari **beberapa anggota**. Salah satu pendekatan agregasi vektor skor adalah **OWA** yang menetapkan bobot terurut pada skor yang **diurutkan** (Yager, 1988). Variasi **rata-rata** adalah kasus khusus yang mudah dijelaskan sebagai **konsensus aritmetik** antar evaluator per sel matriks. OWA dengan kuantifikasi \(Q(r)=r^{\alpha}\) (dengan \(\alpha>1\)) memberi penekanan berbeda pada skor terurut—sesuai parameter yang diatur admin pada modul KMKK di proyek ini.

#### 2.1.4 Technique for Order of Preference by Similarity to Ideal Solution (TOPSIS)

TOPSIS memeringkatkan alternatif berdasarkan **jarak terbobot** ke **solusi ideal positif** dan **solusi ideal negatif** (Hwang & Yoon, 1981). Di sistem penyusun, **matriks keputusan TOPSIS** diambil dari **`aggregated_evaluations`** (bukan langsung dari skor per evaluator), sehingga **TOPSIS** berjalan atas **konsensus kelompok** yang telah distandarisasi.

#### 2.1.5 Kerangka MVC dan Laravel

**Model–View–Controller** memisahkan **logika data**, **tampilan**, dan **aliran permintaan**. **Laravel** menyediakan **routing**, **ORM Eloquent**, **middleware**, **migrasi**, dan **Blade** untuk mengurangi kompleksitas boilerplate (Stauffer, 2022; dokumentasi Laravel).

#### 2.1.6 Basis Data Relasional

Data keputusan disimpan dalam skema relasional (MySQL/MariaDB) dengan relasi antara **periode**, **pelamar**, **kriteria**, **evaluator**, **evaluasi mentah**, **agregasi**, **bobot AHP**, dan **hasil seleksi**.

#### 2.1.7 Ringkasan Variabel Penting dalam Aplikasi

**Tabel 1. Ringkasan variabel dan lokasi penyimpanan (konseptual)**

| Variabel / entitas | Penjelasan singkat | Lokasi penyimpanan (indikatif) |
|:---|:---|:---|
| Skor mentah evaluator | Nilai pada pasangan pelamar × kriteria per evaluator | Tabel `evaluations` (+ `evaluator_id`) |
| Skor agregat kelompok | Satu nilai konsensus per pasangan pada periode | Tabel `aggregated_evaluations` |
| Bobot kriteria periode | Vektor bobot hasil normalisasi AHP | `criteria_weights` (+ `criteria_id`) |
| Hasil TOPSIS | Peringkat, preferensi, jarak ideal, status | `selection_results` |
| Pemetaan kriteria aktif per periode | Kriteria mana yang dipakai + urutan bobot pivot | Pivot periode–kriteria (sesuai migrasi repo) |

### 2.2 Penelitian Terdahulu

Beberapa ragam SPK rekruitment menggunakan gabungan bobot (**AHP**) dan perankingan (**TOPSIS**); beberapa lain memasukkan pembobotan bahasa lunak (**fuzzy**). Penelitian terdahulu yang relevan dengan **topik utama** penyusun:

1. **Saaty (1980)** — fondasi klasik **AHP** bagi pembobot pairwise.  
2. **Hwang & Yoon (1981)** — pengenalan klasik **TOPSIS**.  
3. **Yager (1988)** — **OWA** sebagai agregator terurut.  
4. Kajian rekayasa perangkat lunak terkait **Mining Software Repositories** dan analytics (mis. Zhang dkk., 2021 dalam domain berbeda) mengingatkan pentingnya **dokumentasi sampel**, **bias data**, dan **reproducibility** — prinsip serupa dipakai di sini lewat panduan jalur **`migrate`** + **`db:seed`**.

Ringkasan perbandingan disajikan pada **Tabel 2**.

### 2.3 Tabel Perbandingan

**Tabel 2. Perbandingan fokus penelitian dengan acuan utama**

| No | Sumber utama | Kontribusi teori utama | Konteks mini skripsi ini |
|:---:|:---|:---|:---|
| 1 | Saaty (1980) | AHP pembobot pairwise | Mengisi modul pembobot antar-kriteria tiap **periode** |
| 2 | Hwang & Yoon (1981) | TOPSIS perankingan | Mengisi modul rangking **pelamar** |
| 3 | Yager (1988) | OWA agregasi | Opsi kedua dalam modul **KMKK** |
| 4 | Turban dkk. (2005) | Bingkai SPK | Menjadi panduan struktur aplikasi dukungan |

---

# BAB III  
## METODOLOGI PENELITIAN

### 3.1 Software Development Life Cycle (SDLC)

Penelitian ini mengikuti pendekatan **SDLC model Waterfall** berurutan: **analisis** kebutuhan, **desain** sistem dan basis data, **implementasi**, dan **uji coba** (Pressman & Maxim, 2020).

**Gambar 1.** *(disisipkan manual)* Ilustrasi *Waterfall* — Tahapan: Requirement → Design → Implementation → Verification → Maintenance.

Argumen pemilihan: kebutuhan fungsional utama (alur AHP → KMKK → TOPSIS) telah dikenali di awang-awian sehingga alur dokumentasi bisa linear; perbaikan kecil dapat dilakukan secara iteratif **tanpa mengubah** tujuan penelitian inti.

#### 3.1.1 Analisis

**A. Kebutuhan fungsional**

Kebutuhan fungsional utama disintesis dalam **Tabel 3**.

**Tabel 3. Kebutuhan fungsional utama sistem**

| No | Kebutuhan | Bukti pemenuhan dalam implementasi |
|:---:|:---|:---|
| F1 | Manajemen kriteria & sub-kriteria | Route resource `criteria` (admin) |
| F2 | Manajemen periode & pemetaan kriteria per periode | Route resource `periods` |
| F3 | Data pelamar per periode | Route resource `applicants` |
| F4 | Data evaluator untuk KMKK | Route resource `evaluators` |
| F5 | Input penilaian multi-evaluator | `evaluations.store` dengan `evaluator_id` |
| F6 | Rebuild matriks agregasi kelompok | `POST kmkk/rebuild` + `GroupDecisionAggregator` |
| F7 | Perhitungan AHP & penyimpanan bobot | Halaman `calculations/ahp` |
| F8 | Perhitungan TOPSIS & simpan rangking | Halaman `calculations/topsis` |
| F9 | Laporan dan cetak | `reports.index`, `reports.print` |
| F10 | Pengumuman (opsional komunikasi seleksi) | Resource `announcements` |

**B. Kebutuhan non-fungsional**

Otentikasi via **middleware `auth`**; pembatasan admin via **`role:admin`** pada jalur sensitif (**AHP, TOPSIS, CRUD evaluator, rebuild KMKK**); antarmuka responsif menggunakan **Bootstrap**; kompatibilitas basis data **SQL** sesuai variabel `.env` pada panduan instalasi (**`PANDUAN.md`**).

**C. Adaptasi tahapan proses data *(secara narasi paralel dengan KDD)***

Walaupun sumber utama adalah **basis data aplikasi**, tahapan penyeleksian/transformasi bisa dirangkai narasi paralel seperti **Knowledge Discovery in Databases** (Han, Pei, & Kamber, 2012) untuk memudahkan pembaca menjelaskan alur rekruitment:

**Tabel 4. Pemetaan tahapan proses pada konteks aplikasi penyusun**

| No | Nama tahapan (istilah paralel dengan KDD) | Uraian pada sistem ini |
|:---:|:---|:---|
| 1 | Pemilihan / seleksi | Admin memilih **periode aktif**, kriteria efektif, dan evaluator yang sah |
| 2 | Pembersihan konsistensi | Aturan tidak menghapus evaluator yang masih bertaut ke `evaluations`; validasi matriks penuh sebelum agregasi |
| 3 | Transformasi / agregasi | **Rebuild KMKK** menghasilkan `aggregated_evaluations` dari `evaluations` |
| 4 | **Model / hitung bobot / rangking** | AHP menghasilkan `criteria_weights`; TOPSIS mengisi `selection_results` |
| 5 | Evaluasi pola / dokumentasi keputusan | **Laporan** memuat bobot, agregasi *(perpelamar bisa difilter di UI)*, mentah sampel/traces, serta **cetak** |

#### 3.1.2 Desain

Meliputi **diagram arsitektur** (Gambar 2), **alur utama end-to-end** (Gambar 3), serta **diagram ER konseptual** antar-tabel utama (lihat **`PANDUAN.md`** bagian struktur database).

#### 3.1.3 Implementasi

Implementasi menggunakan **Laravel**, **Composer**, **`php artisan migrate`**, dan **`php artisan serve`**. Frontend server-side **Blade** + **Able Pro**.

#### 3.1.4 Uji Coba

Minimal tiga jalur pembuktian pada lingkungan lokal penyusun:

1. **Uji struktural** — komponen antarmuka tampil konsisten dalam skenario satu periode.  
2. **Uji fungsionalitas jalur utama** — alur penyimpanan → agregasi → AHP → TOPSIS sukses tanpa larangan akses bagi peran yang sah.

3. **Uji konsistensi data demo** — setelah **`db:seed`**, akses **`admin@spk.com`** (password demo sesuai `PANDUAN.md`) mencoba menjalankan setiap blok fitur pembimbing secara berurutan.

---

# BAB IV  
## PERANCANGAN DAN IMPLEMENTASI

### 4.1 Perancangan

#### 4.1.1 Batasan desain sistem

1. Satu rekaman penilaian unik atas kombinasi **(period_id, applicant_id, criteria_id, evaluator_id)**.  
2. **TOPSIS** **tidak langsung membaca** `evaluations` per evaluator, melainkan **`aggregated_evaluations`**. Oleh karena itu **KMKK** wajib sukses sebelum TOPSIS.  
3. **Penyimpanan penilaian** untuk suatu **periode** dapat **invalidasi otomatis** matriks agregat hingga administrator menjalankan **rebuild KMKK** (*sesuai dokumentasi perilaku aplikasi pada `PANDUAN.md`*).

#### 4.1.2 Komponen arsitektur

**Gambar 2.** *(disisipkan manual)* Lapisan:

- Presentasi (**Blade** + Browser) →  
- Applicasi Laravel (**routes → Controller → Policy/Middleware**) →  
- Model & Service (**Eloquent ORM**, servis matematika agregasi) →  
- MySQL (**tabel utama** seperti di Bab II **Tabel 1**).

**Gambar 3.** *(disisipkan manual)* Alur teks paralel dokumentasi panduan aplikasi penyusun *(ringkas)*:

`Login → Setup master (kriteria, periode mapping, pelamar, evaluator)`  
`→ evaluations (tiap evaluator)`  
`→ kmkk rebuild`  
`→ AHP bobot periode`  
`→ TOPSIS hasil rangking`

#### 4.1.3 Rancangan antarmuka utama

Bidang utama antarmuka: **input master**, **form penilaian multi-evaluator**, **panel monitoring KMKK dengan filter dalam tabel**, **aksi hitung AHP/TOPSIS**, serta **cetak**.

#### 4.1.4 Strategi deployment pengembangan lokal

Jalankan `php artisan migrate --seed`; opsional `migrate:fresh --seed` ketika struktur berganti. Produksi luar cakupan mini skripsi.

### 4.2 Implementasi

#### 4.2.1 Ringkasan modul Laravel

Ringkasan disajikan **Tabel 5**.

**Tabel 5. Modul utama dan lokasi akses routing**

| Modul | Rute utama / controller | Hak akses utama |
|:---|:---|:---|
| Dashboard | `/` (`DashboardController`) | auth |
| Penilaian | `evaluations` (`EvaluationController`) | auth (+ konteks evaluator) |
| KMKK | `/kmkk`, `POST kmkk/rebuild` | lihat banyak peran / rebuild admin |
| AHP/TOPSIS | `calculations/ahp`, `calculations/topsis` | admin |
| Laporan | `reports.index`, cetak `{period}` | auth |
| Master data | CRUD periods, applicants, criteria, evaluators… | mayoritas admin |

#### 4.2.2 Logika matematika utama (ringkas pseudocode)

```text
PROSEDUR rebuild_kmkk(period_id, metoda, alpha):
    IF NOT semua_sel_ada_minimal_satu_penilaian(period_id) THEN
        RETURN error konsistensi
    UNTUK setiap kombinasi (pelamar × kriteria) pada periode:
        skor_terurut ← urutankan skor evaluator yang dipakai
        IF metoda == rata THEN agregasi ← mean(skor_terurut)
        ELIF metoda == owa_yager THEN hitung bobot OWA menggunakan Q(r)=r**alpha ...
        ELSE error
        SIMPAN aggregated_evaluations[...]

PROSEDUR hitung_topsis(period_id):
    Muat aggregated_evaluations DAN bobot AHP aktif untuk periode
    Normalisasi matriks; terapkan pembobot vektor bobot normalisasi
    Hitung solusi ideal + & - serta preferensi rangking
    SIMPAN selection_results [...]
```

**Catatan.** Implementasi rinci berada pada berkas seperti `App\Services\GroupDecisionAggregator` serta controller perhitungan di repositori proyek.

#### 4.2.3 Konfigurasi dan dokumentasi aplikasi

Panduan operasional rinci pembaca bisa merujuk ke **`PANDUAN.md`** (alur end-to-end, akun demo, troubleshooting).

---

# BAB V  
## HASIL DAN PEMBAHASAN

### 5.1 Hasil

Berikut **hasil utama** dokumentatif yang bisa dilampiri tangkapan layar:

1. **Master data lengkap**: kriteria (**termasuk sub-kriteria**) dan satu **periode** dengan penetapan pemetaan kriteria aktif.  
2. **Penilaian multi-evaluator** tersimpan per **Evaluator** secara terpisah.  
3. **Matriks agregasi berhasil dibentuk** lewat **`POST`** rebuild KMKK admin memakai metode dipilih (**rata / OWA**).  
4. **Bobot AHP** menghasilkan vektor pembobot konsisten (**CR dalam batas wajar aplikasi**) dan dapat ditampilkan di layar pembobot (*penyunting menulis angka konkret pengujian pembimbing secara manual*).  
5. **Hasil TOPSIS** menghasilkan **rangking** serta **preferensi**.  
6. Modul laporan dapat menampilkan **ringkasan periode**, **bobot**, **detail agregasi per pelamar (dengan filter UI)**, **baris mentah penilaian** per evaluator (**sesuai rancangan filter** dalam versi codebase terkini), serta **hasil peringkat TOPSIS**.

**Gambar 4 & 5** — *disiapkan pembaca/pembimbing dari tangkapan layar.*

### 5.2 Pembahasan

Interpretasi utama:

1. **Pemisahan peran pemangku tugas rekruitment**: evaluator fokus **input**, admin fokus **agregasi** dan komputasi metode utama. Ini selaras kontrol **least privilege**.

2. **OWA sebagai kompromi interpretatif konsensus kelompok** menawarkan variasi pola keputusan yang berbeda dari rata-rata sederhana; sensitivitas terhadap **α** bisa dibahas pembimbing sebagai studi sensitivitas lebih lanjut.

3. **Penyimpanan dua lapisan**: **mentah** (`evaluations`) vs **teragregasi** (`aggregated_evaluations`) memudahkan audit — selaras motif **explainability**.

4. **Keterbatasan**: aplikasi penyusun bukan longitudinal multi-tahun; validasi luar angkatan belum ada; metrik KPI organisasi luar jangkauan laporan akademik sekarang.

### 5.3 Pengujian

#### 5.3.1 Uji struktural

**Tabel 6. Uji struktural (contoh)**

| No | Bidang pengujian | Ekspektasi | Pemenuhan *(diisi pembimbing/pembaca)* |
|:---:|:---|:---|:---|
| 1 | Form penilaian | Sel-sel bisa diisi & disimpan | … |
| 2 | Panel KMKK | Pesanan status agregasi muncul | … |
| 3 | AHP/TOPSIS | Tombol menghitung menghasilkan / mengembalikan error terkendali jika prerequisite tidak terpenuhi | … |

#### 5.3.2 Uji fungsionalitas

**Tabel 7. Uji fungsi jalur inti dengan akun demo seeder *(password lihat `PANDUAN.md`)*

| ID | Skenario | Langkah utama | Observasi ekspektasi |
|:---:|:---|:---|:---|
| S1 | Login admin | Buka `/login`; masuk sebagai **admin demo** | Akses penuh modul administratif |
| S2 | Isi salah satu evaluator | `evaluations` → pilih periode/evaluator | Skor terseimpan |
| S3 | KMKK rebuild lengkap | Penuhi sel matriks; `POST rebuild` metode dipilih | `aggregated_evaluations` bertambah/terupdate |
| S4 | AHP | Jalankan bobot untuk periode | `criteria_weights` terisi |
| S5 | TOPSIS | Jalankan setelah lengkap prerequisite | Rangking baru di `reports`/`results` |
| S6 | Pembatasan akses | Login evaluator tidak dapat buka jalur POST rebuild | HTTP 403 / redirect sesuai desain Laravel |

*(Tulis **Ya/Tidak** & catatan konkret Anda di versi cetak WORD.)*

#### 5.3.3 Uji validasi konsistensi

**Tabel 8. Sinkron matematika sampel mikro *(opsional pembimbing mengisi satu baris contoh numerical cross-check secara manual untuk satu sel atau satu pelamar)***

| No | Sel uji | Rata evaluator mentah *(daftar manual)* | Agregasi tampilan sistem *(harus sama bila pakai average)* | Cocok? |
|:---:|:---|:---:|:---:|:---:|
| 1 | Pelamar **P₁**, **Kriteria C₁**, periode **T₁** *(contoh placeholder)* | | | |

---

# BAB VI  
## KESIMPULAN DAN SARAN

### 6.1 Kesimpulan

1. Telah dibuat **prototype aplikasi SPK** dengan integrasi pipeline **multi-evaluator → agregasi KMKK → AHP bobot → TOPSIS ranking** menggunakan **framework Laravel**.

2. Rancangan data memuat **dual-layer penilaian** (*mentah* vs *agregat*) serta **cetak dokumentasi**.

3. Uji utama berbasis jalur aplikasi dapat dilakukan **reproducible** lewat dokumentasi **`PANDUAN.md`** dalam konteks akademik penyusun ini.

### 6.2 Saran

1. Menambahkan ** PHPUnit / Feature tests** rutin atas servis matematika utama.  
2. Menjelajahi **analisis sensitivitas α OWA** antar pola tim penilai.  
3. **PDF server-side** otomatis (DomPDF atau sejenis) sebagai pelengkap cetak.  
4. **Audit logging** atas perubahan skor penyimpangan besar.  

---

## DAFTAR PUSTAKA

Han, J., Pei, J., & Kamber, M. (2012). *Data mining concepts and techniques* (3rd ed.). Elsevier Morgan Kaufmann.

Hwang, C. L., & Yoon, K. (1981). *Multiple attribute decision making: Methods and applications*. Springer-Verlag Berlin Heidelberg.

Pressman, R. S., & Maxim, B. R. (2020). *Software Engineering: A Practitioner's Approach* (9th ed.). McGraw-Hill.

Saaty, T. L. (1980). *The Analytical Hierarchy Process*. McGraw-Hill.

Stauffer, M. (2024). *Laravel: Up & Running* (edisi sesuai pustaka Anda). O'Reilly Media.

Turban, E., Aronson, J. E., & Liang, T. P. (2005). *Decision support systems and intelligent systems*. Pearson Education.

Yager, R. R. (1988). On ordered weighted averaging aggregation operators in multicriteria decision making. *IEEE Transactions on Systems, Man, and Cybernetics*, *18*(1), 183–190.

Zhang, D., Tao, X., Xu, P., & Wang, X. (2021). Software analytics: Achievements and challenges. *Journal of Computer Science and Technology*, *36*(2), 242–258. https://doi.org/10.1007/s11390-021-0003-4  

Taylor Otwell et al. (2026). *Laravel Documentation* (diakses sesuai versi Laravel proyek). https://laravel.com/docs  

Berkas Lokal penyusun: **`PANDUAN.md`** — instalasi, alur end-to-end, akun demo, struktur modul aplikasi SPK.

---

*Catatan pengetikan Markdown: Pada export ke Microsoft Word atau PDF resmi fakultas, sesuaikan **margin**, **penomoran halaman Roma + Arab**, serta **sitasi** mengikuti **panduan penulisan FMIPA Universitas Pakuan**. Nama ganda pembimbing pengesahan disamakan seperti contoh asli Anda jika pembimbing utama/membimbing akademik lain berbeda—edit manual bagian Pengesahan.*
