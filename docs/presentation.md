---
marp: true
theme: default
class: lead
paginate: true
---

# SPK Seleksi Pelamar — Project Overview

**Tanggal:** 2026-06-25  
**Penulis:** Tim Dev

---

## Tujuan Presentasi

- Menjelaskan arsitektur aplikasi seleksi pelamar
- Ringkasan data flow: evaluasi → agregasi → TOPSIS → hasil
- Perubahan pada Naive Bayes untuk prediksi lintas-periode
- Masalah seeder saat ini dan rekomendasi perbaikan
- Langkah selanjutnya untuk integrasi dan pengujian

---

## Arsitektur Tingkat Tinggi

- Laravel backend (Eloquent models)
  - `Applicant`, `Evaluation`, `AggregatedEvaluation`, `SelectionPeriod`, `SelectionResult`
- Services:
  - `GroupDecisionAggregator` (agregasi evaluator)
  - `TopsisService` (perhitungan ranking & penyimpanan `SelectionResult`)
  - `BayesPredictor` (Naive Bayes untuk prediksi)
- UI: Blade views (analytics, prediction)

---

## Data Flow

```mermaid
flowchart LR
  E[Evaluators -> Evaluations] --> A[AggregatedEvaluation]
  A --> T[TopsisService]
  T --> R[SelectionResult (rank, status)]
  A --> B[BayesPredictor (train/predict)]
  B --> P[Prediction View]
```

---

## Masalah pada Dummy Seeder Saat Ini

- Seeder monolitik: 10 periode dengan data hard-coded
- Kriteria berbeda per periode (TU1..TU7 vs IT1..IT7) → fitur tidak konsisten
- Naive Bayes kesulitan belajar lintas-periode tanpa fitur bersama
- Beberapa periode menunjukkan konsistensi Bayes vs TOPSIS rendah (mis. periode 8 & 10)

---

## Perubahan yang Telah Dilakukan pada `BayesPredictor`

- Memperkenalkan `FEATURE_MAP` untuk memetakan kriteria job-specific → fitur umum
- Mengelompokkan `AggregatedEvaluation` menurut fitur bersama
- Menggunakan bobot kriteria (`CriteriaWeight`) saat menghitung skor fitur (weighted avg)
- Menjaga smoothing (Laplace) dan normalisasi probabilitas

---

## Contoh `FEATURE_MAP` (singkat)

- education: [TU1, IT1]
- communication: [TU2, IT4]
- documentation: [TU3]
- technical: [TU4, IT2, IT7]
- experience: [TU6, IT6]
- interview: [TU7, IT5]

---

## Hasil Evaluasi Awal (seed data sekarang)

- Periode 2: 100% konsistensi Bayes vs TOPSIS
- Periode 3..9: banyak di 83% area
- Periode 8 & 10: konsistensi sekitar 66% → butuh perbaikan data/feature

---

## Rekomendasi Perombakan Seeder (best-practice)

1. Modular seeder: pisahkan pembuatan kriteria, pelamar, evaluator, evaluasi, bobot
2. Gunakan generator profil pelamar (variasi IPK, pengalaman) — bukan hard-coded semua
3. Standarkan fitur bersama (mapping) sehingga Naive Bayes bisa belajar lintas-periode
4. Kontrol distribusi kelas (`lulus`/`tidak_lulus`) sehingga tidak terlalu imbalanced
5. Pastikan setiap pasangan pelamar–kriteria memiliki minimal 1 evaluasi
6. Tambahkan opsi untuk menghasilkan noise / evaluator bias kecil untuk realisme

---

## Contoh Struktur Seeder Modular (draft)

- `CriteriaSeeder` — buat `TU*` dan `IT*` + subcriteria
- `EvaluatorSeeder` — buat evaluator, urutan, role
- `ApplicantFactory` — generator profil realistis
- `PeriodSeeder` — buat periode ringkas, attach kriteria & bobot
- `EvaluationSeeder` — isi evaluasi per periode via `seedMultiKmkkEvaluations` style
- After seed: call `GroupDecisionAggregator::rebuild()` then `TopsisService::calculateAndSave()`

---

## Run & Verify (suggested commands)

```bash
# reset DB and seed
php artisan migrate:fresh --seed
# rebuild aggregates (if needed)
php artisan tinker --execute "app(App\\Services\\GroupDecisionAggregator::class)->rebuild(1,'average');"
# calculate TOPSIS for a period
php artisan tinker --execute "app(App\\Services\\TopsisService::class)->calculateAndSave(1,0);"
```

---

## Next Steps

- [ ] Buan draft seeder modular (AI atau manual)
- [ ] Generate new dummy dataset and reseed
- [ ] Run consistency checks Bayes vs TOPSIS automatically
- [ ] Tuning `FEATURE_MAP` dan weighting jika perlu

---

## Appendix — Files to review

- `app/Services/BayesPredictor.php`  
- `app/Services/TopsisService.php`  
- `app/Services/GroupDecisionAggregator.php`  
- `database/seeders/DatabaseSeeder.php`

---

# Terima kasih

- Butuh versi file yang bisa diekspor ke PowerPoint (.pptx)? Saya bisa konversi Markdown ke PPTX (Marp → PPTX) setelah kamu setuju pada konten.
