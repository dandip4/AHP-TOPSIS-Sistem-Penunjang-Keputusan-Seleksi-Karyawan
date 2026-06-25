# LAPORAN PROGRES PROYEK AKHIR
## Sistem Pendukung Keputusan (SPK) Hibrid Model-Driven dan Data-Driven

---

## 1. JUDUL PROYEK AKHIR

### **"Sistem Pendukung Keputusan Hibrid Multi-Model untuk Seleksi Pelamar Berbasis AHP-TOPSIS dengan Agregasi KMKK dan Analitik Data Historis"**

**Alternatif Judul:**
- "SPK Seleksi Pelamar Terintegrasi: Model-Driven (AHP-TOPSIS-KMKK) dan Data-Driven dengan Analisis Trend & Prediksi Berbasis Data Historis"
- "Aplikasi SPK Seleksi Pelamar: Kombinasi AHP, TOPSIS, Multi-Evaluator Decision Making, dan Dashboard Analitik Real-Time"

**Tipe Proyek:** SPK Hibrid (Model-Driven + Data-Driven) dengan Big Data Analytic

---

## 2. SUMBER, DESKRIPSI DATA RINCI & ALGORITMA

### 2.1 Sumber Data

**Jenis Data:**
- **Data Input Operasional:** Kriteria penilaian, data pelamar, skor evaluasi dari evaluator
- **Data Proses:** Matriks perbandingan AHP, bobot kriteria, skor agregasi KMKK
- **Data Output:** Ranking pelamar, status kelulusan (lulus/tidak lulus)
- **Data Historis:** Riwayat selection_results dari periode sebelumnya untuk predictive analytics

**Volume Data:**
- Dapat menampung ratusan pelamar per periode
- Dapat memproses ribuan evaluasi (n pelamar × m kriteria × k evaluator)
- Akumulasi data dari 10+ periode untuk analisis historis dan trend

---

### 2.2 Deskripsi Data Rinci (Tabel Database)

| # | Tabel | Deskripsi | Kolom Utama | Foreign Key |
|---|-------|-----------|-------------|------------|
| 1 | `selection_periods` | Periode seleksi (contoh: Seleksi 2024, Seleksi Supervisor 2024) | name, position, start_date, end_date, status (draft/open/closed/completed), created_by | users.id |
| 2 | `criteria` | Kriteria penilaian master (Pendidikan, Pengalaman, Komunikasi, dll) | code, name, type (benefit/cost), importance (1-9), is_active | - |
| 3 | `sub_criteria` | Sub-detail kriteria (Contoh: Pendidikan → S1/S2/S3) | criteria_id, name, value, description | criteria.id |
| 4 | `applicants` | Data pelamar (nama, email, phone, gender, education, GPA, etc) | period_id, name, email, phone, gender, birth_date, education, major, gpa, age, address | selection_periods.id |
| 5 | `evaluators` | Data penilai yang aktif (HR Manager, Supervisor, Direktur) | code, name, role_label, user_id, sort_order, is_active | users.id |
| 6 | `evaluations` | Skor individual evaluator untuk setiap pelamar per kriteria (skala 1-5) | period_id, applicant_id, criteria_id, evaluator_id, score | period, applicant, criteria, evaluator |
| 7 | `pairwise_comparisons` | Matriks perbandingan berpasangan untuk AHP (Kriteria A dibanding Kriteria B) | period_id, criteria_row_id, criteria_col_id, value | period, criteria_row, criteria_col |
| 8 | `criteria_weights` | Bobot hasil AHP untuk setiap kriteria per periode (jumlah = 1) | period_id, criteria_id, weight (0-1) | period, criteria |
| 9 | `aggregated_evaluations` | Skor agregasi dari semua evaluator per pelamar/kriteria (hasil KMKK) | period_id, applicant_id, criteria_id, aggregated_score, aggregation_method, evaluator_count_used | period, applicant, criteria |
| 10 | `selection_results` | Hasil ranking akhir TOPSIS untuk setiap pelamar per periode | period_id, applicant_id, preference_value, positive_distance, negative_distance, rank, status (lulus/tidak_lulus) | period, applicant |
| 11 | `announcements` | Pengumuman hasil seleksi atau informasi terkait | period_id, title, content, is_published, published_at, created_by | period, users |
| 12 | `selection_period_criteria` | Pivot table: kriteria mana saja yang aktif di setiap periode dan urutannya | selection_period_id, criteria_id, sort_order | period, criteria |

**Total 12 tabel inti aplikasi**

---

### 2.3 Algoritma Model-Driven (Sudah Implementasi)

#### **A. Analytic Hierarchy Process (AHP)**

**Tujuan:** Menentukan bobot kriteria secara sistematis dan konsisten

**Formulasi Matematis:**

```
1. Buat Matriks Perbandingan Berpasangan (A):
   A = [a_ij] dimana a_ij = importance_i / importance_j

2. Normalisasi Kolom:
   a'_ij = a_ij / Σ(kolom j)

3. Hitung Bobot Kriteria (Priority Vector):
   w_i = Σ(baris i) / n

4. Validasi Konsistensi:
   - Hitung Lambda Max: λ_max = Σ(A × w) / Σ(w)
   - Consistency Index: CI = (λ_max - n) / (n - 1)
   - Consistency Ratio: CR = CI / RI
   - Valid jika CR ≤ 0.1
```

**Input:** Matriks perbandingan berpasangan atau importance value setiap kriteria
**Output:** Bobot kriteria (weight), CI, CR, validasi konsistensi
**Implementasi:** `app/Services/AhpService.php`

---

#### **B. Kelompok Keputusan (KMKK) - Agregasi Multi-Evaluator**

**Tujuan:** Menyatukan penilaian dari banyak evaluator menjadi skor tunggal per pelamar/kriteria

**Metode 1: Average (Rata-rata Aritmatik)**
```
score_agregasi = mean(skor_evaluator1, skor_evaluator2, ..., skor_evaluatorK)
```

**Metode 2: OWA Yager (Ordered Weighted Averaging)**
```
1. Urutkan skor ascending: s_1 ≤ s_2 ≤ ... ≤ s_n
2. Hitung bobot Yager: w_j = Q(j/n) - Q((j-1)/n)
   dimana Q(r) = r^α dengan α > 1
3. Agregasi: score = Σ(w_j × s_j)
```

**Validasi:** Semua sel (pelamar × kriteria) harus terisi oleh minimal 1 evaluator
**Input:** Tabel evaluations (skor dari semua evaluator)
**Output:** Tabel aggregated_evaluations (skor tunggal per sel)
**Implementasi:** `app/Services/GroupDecisionAggregator.php`

---

#### **C. Technique for Order of Preference by Similarity to Ideal Solution (TOPSIS)**

**Tujuan:** Ranking pelamar berdasarkan jarak ke solusi ideal positif dan negatif

**Formulasi Matematis:**

```
1. Normalisasi Matriks Keputusan:
   r_ij = x_ij / √(Σ x_ij²)

2. Matriks Berbobot:
   v_ij = r_ij × w_j

3. Tentukan Ideal Positif (A+) dan Negatif (A-):
   - Untuk benefit: A+ = max(v_ij), A- = min(v_ij)
   - Untuk cost:   A+ = min(v_ij), A- = max(v_ij)

4. Hitung Jarak ke Ideal:
   D+ = √(Σ(v_ij - A+)²)
   D- = √(Σ(v_ij - A-)²)

5. Hitung Preference Value:
   C_i = D- / (D+ + D-)

6. Ranking: Sort descending berdasarkan C_i
```

**Input:** Agregat KMKK + bobot AHP
**Output:** Ranking pelamar, status lulus/tidak lulus (berdasarkan threshold)
**Implementasi:** `app/Services/TopsisService.php`

---

### 2.4 Algoritma Data-Driven (Rencana Pengembangan)

#### **A. Predictive Analytics - Naive Bayes**

**Tujuan:** Prediksi probabilitas kelulusan pelamar berdasarkan data historis dari periode sebelumnya

**Formulasi Matematis:**
```
P(Lulus | score_K1, score_K2, ..., score_Kn) = 
    P(Lulus) × ∏ P(score_Ki | Lulus) / P(scores)

Asumsi: Setiap skor kriteria independen (naive assumption)

Proses Training:
1. Ambil data historis: evaluations + selection_results dari N periode sebelumnya
2. Hitung P(Lulus) = jumlah_lulus / total_pelamar
3. Hitung P(score_Ki | Lulus) = f(score) / jumlah_lulus (dengan binning score 1-5)
4. Hitung P(score_Ki | Tidak Lulus) untuk kelas negatif

Prediksi:
1. Input: skor evaluasi pelamar baru
2. Hitung probability untuk class "Lulus" dan "Tidak Lulus"
3. Output: confidence score, rekomendasi
```

**Data Training:** 
- `selection_results` dari 5-10 periode terakhir
- Label: status = 'lulus' atau 'tidak_lulus'
- Features: skor agregat dari 5-10 kriteria

**Output:** Confidence score (0-100%), rekomendasi kelulusan untuk setiap pelamar

**Implementasi:** `app/Services/BayesPredictor.php` (baru)

---

### 2.5 Big Data Analytics & Insights

#### **A. Descriptive Analytics (Dashboard Real-Time)**

```
1. Distribusi Skor:
   - Histogram skor per kriteria (min, max, mean, median, stdev)
   - Boxplot untuk identifikasi outlier
   - Distribusi gender, pendidikan, GPA pelamar

2. Statistik Periode:
   - Total pelamar, evaluator
   - Persentase kelulusan
   - Rata-rata skor per kriteria
   - Ranking top 10 pelamar

3. Trend Historis:
   - Trend bobot kriteria dari periode ke periode
   - Trend persentase lulus dari tahun ke tahun
   - Identifikasi kriteria paling diskriminatif
```

**Query SQL untuk Analytics:**
```sql
-- Rata-rata skor per kriteria across all periods
SELECT c.code, c.name, AVG(ae.aggregated_score) as avg_score
FROM aggregated_evaluations ae
JOIN criteria c ON ae.criteria_id = c.id
GROUP BY ae.criteria_id
ORDER BY avg_score DESC;

-- Distribusi kelulusan per periode
SELECT sp.name as period_name, 
       COUNT(CASE WHEN sr.status = 'lulus' THEN 1 END) as lulus,
       COUNT(CASE WHEN sr.status = 'tidak_lulus' THEN 1 END) as tidak_lulus,
       ROUND(100.0 * COUNT(CASE WHEN sr.status = 'lulus' THEN 1 END) / COUNT(*), 2) as pct_lulus
FROM selection_results sr
JOIN selection_periods sp ON sr.period_id = sp.id
GROUP BY sr.period_id
ORDER BY sp.id DESC;

-- Variabilitas evaluator (siapa yang paling/paling tidak stringent?)
SELECT e.name as evaluator_name, 
       ROUND(AVG(ev.score), 2) as avg_score,
       ROUND(STDDEV(ev.score), 2) as stddev_score
FROM evaluations ev
JOIN evaluators e ON ev.evaluator_id = e.id
GROUP BY ev.evaluator_id
ORDER BY avg_score DESC;
```

#### **B. Comparative Analytics**

```
1. Perbandingan Metode:
   - TOPSIS ranking vs Naive Bayes prediction
   - Konsistensi: berapa persen pelamar yang ranked top 10 di TOPSIS juga diprediksi tinggi Bayes?
   - Jika ada inkonsistensi: ada kemungkinan bias evaluasi

2. Sensitivity Analysis:
   - Jika bobot kriteria berubah ±10%, ranking berubah berapa posisi?
   - Kriteria mana yang paling berpengaruh terhadap ranking final?
```

#### **C. Prescriptive Analytics (Rekomendasi)**

```
1. Simulasi Target Kelulusan:
   - Jika target lulus = N%, bobot optimal untuk setiap kriteria?
   - Simulasi threshold preferensi value TOPSIS

2. Rekomendasi Kriteria:
   - Kriteria mana yang paling diskriminatif?
   - Kriteria mana yang bisa dihilangkan tanpa mengubah ranking signifikan?

3. Quality Assurance:
   - Flag: evaluator dengan variabilitas skor terlalu tinggi/rendah
   - Flag: pelamar dengan skor ekstrim (sangat tinggi/rendah) di satu kriteria
```

---

## 3. ARSITEKTUR SPK HIBRID BIG DATA ANALYTIC & STRATEGI SPK

### 3.1 Arsitektur 3-Tier

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         UI LAYER (Web Interface)                             │
│  ┌──────────────────┬────────────────────┬──────────────────────┬──────────┐
│  │   Autentikasi    │   Evaluasi Form    │  Perhitungan Bobot   │ Laporan  │
│  │  (Login/Logout)  │  (Multi-Evaluator) │   (AHP / TOPSIS)     │ & Export │
│  └──────────────────┴────────────────────┴──────────────────────┴──────────┘
│  ┌──────────────────┬────────────────────┬──────────────────────┬──────────┐
│  │   Dashboard      │  Manajemen Data    │   Analitik & Insight │ Admin    │
│  │  (Chart, Stats)  │ (Periode, Criteria,│  (Trend, Prediksi,   │ Panel    │
│  │                  │  Pelamar, Evaluator)  Sensitivity)        │          │
│  └──────────────────┴────────────────────┴──────────────────────┴──────────┘
└─────────────────────────────────────────────────────────────────────────────┘
                                      ↓↑
┌─────────────────────────────────────────────────────────────────────────────┐
│                         MODEL BASE LAYER                                     │
│  ┌─────────────────────────────────────────────────────────────────────────┐
│  │ Model-Driven Decision Algorithms:                                       │
│  │  • AhpService: Perhitungan bobot kriteria + validasi konsistensi        │
│  │  • GroupDecisionAggregator: Agregasi multi-evaluator (Average/OWA)      │
│  │  • TopsisService: Ranking pelamar berdasarkan jarak solusi ideal        │
│  └─────────────────────────────────────────────────────────────────────────┘
│  ┌─────────────────────────────────────────────────────────────────────────┐
│  │ Data-Driven Analytics Engines:                                          │
│  │  • BayesPredictor: Prediksi kelulusan berbasis historis                │
│  │  • AnalyticsService: Agregasi data, trend, outlier detection           │
│  │  • SensitivityAnalyzer: Simulasi perubahan parameter, rekomendasi      │
│  └─────────────────────────────────────────────────────────────────────────┘
└─────────────────────────────────────────────────────────────────────────────┘
                                      ↓↑
┌─────────────────────────────────────────────────────────────────────────────┐
│                         DATABASE LAYER                                       │
│  ┌──────────────────────────────────────────────────────────────────────────┐
│  │ Operational Database (MySQL/PostgreSQL):                                │
│  │  • Users, Evaluators: Data pengguna & autentikasi                      │
│  │  • Periods, Criteria, Applicants: Data master seleksi                  │
│  │  • Evaluations: Data transaksi penilaian                               │
│  │  • Pairwise Comparisons: Data proses AHP                               │
│  │  • Aggregated Evaluations: Data proses KMKK                            │
│  │  • Selection Results: Data hasil akhir TOPSIS                          │
│  │  • Announcements: Data pengumuman                                       │
│  │  • Selection Period Criteria: Pivot kriteria per periode               │
│  └──────────────────────────────────────────────────────────────────────────┘
│  ┌──────────────────────────────────────────────────────────────────────────┐
│  │ Analytical Database (untuk Big Data Insights):                          │
│  │  • Materialized Views: pre-computed metrics & aggregations              │
│  │  • Historical Data: archives dari 10+ periode untuk trend analysis      │
│  │  • Denormalized Tables: untuk fast query analytics                      │
│  └──────────────────────────────────────────────────────────────────────────┘
└─────────────────────────────────────────────────────────────────────────────┘
```

### 3.2 Strategi SPK Hibrid

#### **Tahap 1: Persiapan & Konfigurasi Periode**
```
1. Admin definisikan periode seleksi (nama, posisi, tanggal mulai/akhir)
2. Admin pilih kriteria aktif untuk periode ini & atur urutannya
3. Admin input bobot awal kriteria (opsional, bisa auto-generated dari importance)
4. Admin assign evaluator yang aktif untuk periode ini
5. System load data master: kriteria, sub-kriteria dari DB
```
**Keputusan:** Periode siap untuk evaluasi

---

#### **Tahap 2: Penilaian Kolektif (KMKK Input)**
```
1. Setiap evaluator login ke sistem
2. Evaluator isi skor untuk setiap pasangan (pelamar, kriteria) → skala 1-5
3. System catat evaluations ke DB dengan timestamp
4. Admin bisa melihat progress: berapa % sel yang sudah terisi
5. Setelah semua evaluator selesai → lanjut ke agregasi
```
**Validasi:** Minimal satu evaluator per sel (pelamar × kriteria) harus terisi

---

#### **Tahap 3: Agregasi KMKK**
```
1. Admin trigger rebuild agregasi
2. GroupDecisionAggregator mengambil semua skor evaluasi per (pelamar, kriteria)
3. Pilih metode agregasi:
   - Average (rata-rata aritmatik) → lebih demokratis
   - OWA Yager (dengan alpha tuning) → bisa prefer skor tinggi/rendah
4. Hitung aggregated_score untuk setiap sel
5. Simpan ke aggregated_evaluations dengan metadata (metode, jumlah evaluator)
6. **Data-Driven Insight**: Hitung variabilitas skor antar evaluator
   - Jika stdev tinggi → ada disagreement antar evaluator → mungkin kriteria tidak jelas
   - Identifikasi evaluator outlier untuk diskusi tim
```
**Output:** Skor agregat siap untuk AHP & TOPSIS

---

#### **Tahap 4: Perhitungan Bobot AHP**
```
1. Admin buat/update matriks perbandingan berpasangan
   - Opsi 1: Input manual perbandingan setiap pasang kriteria
   - Opsi 2: Auto-generate dari Criteria.importance
2. AhpService hitung:
   - Normalized matrix
   - Priority vector (bobot kriteria)
   - Lambda max, CI, CR
3. Validasi CR ≤ 0.1 (konsistensi)
   - Jika CR > 0.1 → kriteria inconsistent → minta tim review & input ulang
   - Jika CR ≤ 0.1 → valid, lanjut
4. Simpan bobot ke criteria_weights
5. **Data-Driven Insight**: Bandingkan bobot AHP sekarang vs periode sebelumnya
   - Perubahan signifikan? → ada perubahan prioritas atau kesalahan input?
   - Ploting trend bobot dari periode ke periode
```
**Output:** Bobot kriteria yang konsisten & tervalidasi

---

#### **Tahap 5: Ranking TOPSIS & Prediksi Bayes**
```
1. Input: 
   - Agregat KMKK (aggregated_evaluations)
   - Bobot AHP (criteria_weights)
   - Tipe kriteria (benefit/cost)
2. TopsisService hitung:
   - Normalisasi, weighted matrix
   - Ideal positive/negative solutions
   - Jarak ke ideal solutions
   - Preference value & ranking
3. Simpan hasil ke selection_results (rank, preference_value, status lulus/tidak)
4. **Data-Driven**: Jalankan Naive Bayes prediction (RENCANA)
   - Baca historis dari N periode sebelumnya
   - Training: compute P(Lulus|scores)
   - Prediksi: untuk setiap pelamar baru, hitung confidence score
   - Bandingkan: ranking TOPSIS vs confidence Bayes
   - Jika ada outlier → ada pelamar rank tinggi tapi confidence rendah? → perlu review
```
**Output:** Ranking final + status lulus/tidak, plus insights dari data-driven

---

#### **Tahap 6: Analitik Dashboard & Pengambilan Keputusan**
```
1. **Descriptive Dashboard:**
   - Visualisasi: distribusi skor per kriteria (histogram, boxplot)
   - Top 10 pelamar ranking
   - Statistik: jumlah lulus, tidak lulus, persentase
   - Evaluator performance: siapa paling stringent/lenient?

2. **Trend Analysis (Multi-Period):**
   - Chart: trend bobot kriteria dari periode ke periode
   - Chart: trend persentase lulus across periods
   - Identifikasi: kriteria paling diskriminatif dalam sejarah

3. **Predictive Insights:**
   - Confidence score Bayes untuk setiap pelamar
   - Flag: pelamar dengan diskrepansi antara TOPSIS ranking vs Bayes confidence
   - Rekomendasi: pelamar mana yang confident untuk lulus, yang doubtful

4. **Sensitivity Analysis:**
   - Simulasi: jika bobot berubah ±10%, ranking berubah berapa?
   - Slider: adjust threshold preference value, lihat berapa yang lulus
   - Rekomendasi: bobot optimal untuk mencapai target lulus = N%

5. **Quality Control:**
   - Flag evaluator dengan skor variance terlalu tinggi/rendah
   - Flag pelamar dengan skor ekstrim di satu kriteria
   - Rekomendasi: re-evaluasi atau validasi data

6. **Admin Decision Making:**
   - Review: ranking TOPSIS + insights
   - Verifikasi: confidence Bayes + sensitivity analysis
   - Finalize: keputusan lulus/tidak lulus untuk setiap pelamar
   - Catat: keputusan final di selection_results.status
```

---

#### **Tahap 7: Pengumuman & Laporan**
```
1. Admin publish pengumuman hasil seleksi
2. Generate laporan:
   - PDF: ranking, foto pelamar, kriteria scores
   - CSV/Excel: raw data untuk arsip/analisis lebih lanjut
3. Archive: simpan selection_results sebagai data historis untuk periode berikutnya
```

---

### 3.3 Keputusan & Rekomendasi SPK

| Fase | Keputusan | Data Sumber | Algoritma | Tingkat Kepercayaan |
|------|-----------|-------------|-----------|-------------------|
| Input | Periode & Kriteria siap? | Criteria master, admin input | - | High (manual) |
| Agregasi | Penilaian kolektif valid? | Evaluations, count per cell | KMKK Average/OWA | High |
| Bobot | Bobot kriteria konsisten? | Pairwise Comparisons | AHP + CR validation | High (CR ≤ 0.1) |
| Ranking | Pelamar A lebih unggul dari B? | Agregat KMKK, bobot AHP | TOPSIS + normalisasi | High |
| Prediksi | Pelamar A likely lulus? | Historis, skor sekarang | Naive Bayes | Medium (depends on history) |
| Rekomendasi | Final keputusan lulus/tidak? | TOPSIS ranking + Bayes + Sensitivity | Kombinasi all insights | Very High |

---

## 4. CODING YANG SUDAH BERJALAN

### 4.1 Core Components - Model-Driven (✅ Sudah Implementasi)

#### **A. Authentication & Authorization**
```
File: app/Http/Controllers/AuthController.php
- showLogin(): tampilkan halaman login
- login(): validasi credentials, create session
- logout(): invalidate session

Roles:
- admin: akses penuh (setup, perhitungan, admin panel)
- direktur: view-only laporan
- evaluator: isi evaluasi untuk diri sendiri
```

#### **B. Master Data Management**
```
File: app/Http/Controllers/SelectionPeriodController.php
- index(): list periode seleksi
- create() / store(): buat periode baru + pilih kriteria
- edit() / update(): ubah periode + sync kriteria
- destroy(): hapus periode

File: app/Http/Controllers/CriteriaController.php
- index(): list kriteria master
- create() / store(): tambah kriteria
- edit() / update(): edit kriteria
- destroy(): hapus kriteria
- toggleActive(): aktif/non-aktif kriteria
- storeSubCriteria() / destroySubCriteria(): manajemen sub-kriteria

File: app/Http/Controllers/ApplicantController.php
- index(): list pelamar per periode
- create() / store(): tambah pelamar
- edit() / update(): edit pelamar
- destroy(): hapus pelamar

File: app/Http/Controllers/EvaluatorController.php
- CRUD evaluator (admin assign siapa yang jadi penilai)
```

#### **C. Evaluation Input Form**
```
File: app/Http/Controllers/EvaluationController.php
- index(): tampilkan form evaluasi
  - Admin bisa pilih evaluator mana yang isi
  - Evaluator hanya bisa isi untuk diri sendiri
- store(): simpan skor evaluasi ke DB
  - Validasi: score 1-5, kriteria valid
  - Clear agregat KMKK lama (auto-reset)

Form UI:
- Select periode
- Select evaluator (admin only)
- Grid: pelamar × kriteria
- Input: score 1-5 untuk setiap cell
```

#### **D. Calculation Engine**
```
File: app/Services/AhpService.php
- generatePairwiseFromImportance(): auto-generate matriks dari importance
- savePairwiseMatrix(): simpan matriks perbandingan
- calculateWeights(): hitung bobot + validasi CR

File: app/Services/GroupDecisionAggregator.php
- clearAggregatesForPeriod(): clear agregat lama
- rebuild(): bangun ulang agregat KMKK (Average / OWA Yager)
- isAggregateMatrixComplete(): cek kelengkapan data
- linguisticOwaAscending(): hitung OWA Yager

File: app/Services/TopsisService.php
- getCalculationData(): pre-compute TOPSIS (tanpa simpan)
- calculateAndSave(): hitung TOPSIS + simpan selection_results

File: app/Http/Controllers/CalculationController.php
- ahp(): tampilkan halaman AHP
- calculateAhp(): trigger perhitungan AHP
- topsis(): tampilkan halaman TOPSIS
- calculateTopsis(): trigger perhitungan TOPSIS
- results(): tampilkan ranking hasil TOPSIS
```

#### **E. Reporting & Dashboard**
```
File: app/Http/Controllers/ReportController.php
- index(): tampilkan laporan detail
  - List pelamar dengan ranking
  - Skor agregat per kriteria
  - Status kelulusan
  - Filter: per periode, per pelamar
- print(): generate PDF laporan untuk print

File: app/Http/Controllers/DashboardController.php
- index(): dashboard statistik
  - Widgets: total periode, pelamar, kriteria, lulus, tidak lulus
  - Charts: 
    - Pelamar per periode
    - Distribusi gender, pendidikan, GPA
    - Status periode (draft, open, closed, completed)
    - Bobot kriteria (last period)
    - Top 10 ranking
    - Rata-rata skor per kriteria
    - Distribusi skor (histogram)
```

#### **F. Database Models & Relationships**
```
File: app/Models/
- User.php: user accounts + role
- SelectionPeriod.php: periode seleksi + relasi ke criteria, applicants, evaluations, results
- Criteria.php: master kriteria + sub_criteria, importance, type (benefit/cost)
- SubCriteria.php
- Applicant.php: data pelamar + relasi ke evaluations, results
- Evaluator.php: data penilai + relasi ke user, evaluations
- Evaluation.php: skor individual evaluator
- PairwiseComparison.php: matriks AHP
- CriteriaWeight.php: bobot hasil AHP
- AggregatedEvaluation.php: skor agregat KMKK
- SelectionResult.php: ranking final TOPSIS
- Announcement.php: pengumuman
- SelectionPeriodCriteria.php: pivot kriteria per periode
```

---

### 4.2 Status Implementation Summary

| Komponen | Status | File | Catatan |
|----------|--------|------|---------|
| **Model-Driven** | | | |
| AHP Bobot Kriteria | ✅ DONE | AhpService.php | CR validation included |
| KMKK Agregasi | ✅ DONE | GroupDecisionAggregator.php | Average + OWA Yager |
| TOPSIS Ranking | ✅ DONE | TopsisService.php | Benefit/cost support |
| Evaluasi Input | ✅ DONE | EvaluationController.php | Multi-evaluator |
| Master Data (Periode, Kriteria, Pelamar) | ✅ DONE | SelectionPeriodController.php, CriteriaController.php, ApplicantController.php | CRUD complete |
| Laporan Dasar | ✅ DONE | ReportController.php | List ranking + details |
| Dashboard Statistik | ✅ DONE | DashboardController.php | 8+ charts |
| **Data-Driven** | | | |
| Naive Bayes Prediction | 📝 PLANNED | - | Training dari historis |
| Enhanced Analytics | 📝 PLANNED | - | Trend, outlier, sensitivity |
| Analytics Dashboard | 📝 PLANNED | - | Real-time insights |
| **Infrastructure** | | | |
| Database Schema | ✅ DONE | migrations/ | 12 tabel inti |
| Authentication | ✅ DONE | AuthController.php | Role-based access |
| Frontend UI | ✅ DONE | resources/views/ | Blade templates |

---

### 4.3 Fitur Yang Berjalan - Test Case

#### **Test Scenario: Seleksi Supervisor 2024**

**Setup:**
- Periode: "Seleksi Supervisor 2024" (2024-01-01 ~ 2024-02-01)
- Kriteria: Pendidikan (benefit), Pengalaman (benefit), Komunikasi (benefit), Kedisiplinan (benefit) → importance [3, 5, 4, 3]
- Pelamar: 5 orang (A, B, C, D, E)
- Evaluator: 3 orang (HR Manager, Supervisor, Direktur)

**Flow:**
1. Admin login → Setup periode + kriteria
2. Admin assign 3 evaluator, kasih akses evaluasi
3. 3 Evaluator isi skor untuk 5 pelamar × 4 kriteria → 60 evaluasi (3 × 5 × 4)
4. Admin rebuild agregasi KMKK → avg_score per sel
5. Admin jalankan AHP → bobot kriteria (ex: [0.30, 0.40, 0.20, 0.10])
6. Admin jalankan TOPSIS → ranking final
7. Dashboard tampilkan hasil + insights

**Expected Output:**
- Ranking: A (rank 1, pref=0.85), B (rank 2, pref=0.72), C (rank 3, pref=0.68), D (rank 4, pref=0.55), E (rank 5, pref=0.42)
- Status: A, B, C = lulus; D, E = tidak lulus
- Laporan PDF: tabel ranking + chart distribusi skor

---

### 4.4 Known Limitations & Future Work

```
Limitations Saat Ini:
1. Dashboard analitik terbatas → hanya statistik dasar
2. Tidak ada predictive analytics (Bayes) → belum ada prediksi historis
3. Tidak ada sensitivity analysis → belum bisa simulasi perubahan parameter
4. Tidak ada API → sistem standalone, tidak terintegrasi sistem lain
5. Tidak ada mobile version → hanya web desktop

Rencana Pengembangan (Data-Driven & Big Data):
1. BayesPredictor service + training module
2. Advanced analytics: trend, outlier detection, correlation
3. Sensitivity analyzer: simulasi parameter changes
4. Enhanced dashboard: real-time insights, alerts
5. Export data: CSV, Excel untuk BI tools
6. API: REST API untuk integrasi eksternal
7. Monitoring: audit log, evaluator performance tracking
```

---

## KESIMPULAN

Proyek **SPK Seleksi Pelamar Hibrid Model-Driven & Data-Driven** sudah memiliki:

✅ **Model-Driven yang Lengkap:**
- AHP untuk bobot kriteria dengan validasi konsistensi
- KMKK untuk agregasi multi-evaluator
- TOPSIS untuk ranking pelamar

✅ **Database yang Komprehensif:**
- 12 tabel inti mendukung SPK end-to-end
- Kapasitas untuk ribuan evaluasi dari ratusan pelamar

✅ **Interface yang User-Friendly:**
- Form evaluasi intuitif
- Dashboard statistik real-time
- Laporan cetak PDF

📝 **Rencana Pengembangan (Phase 2):**
- Naive Bayes untuk predictive analytics
- Enhanced analytics & dashboard
- Sensitivity analysis & simulasi

Aplikasi ini sudah siap untuk **deployment dan testing** pada periode seleksi real. Dokumentasi laporan mini skripsi dapat disusun berdasarkan struktur yang telah dijelaskan di atas.

