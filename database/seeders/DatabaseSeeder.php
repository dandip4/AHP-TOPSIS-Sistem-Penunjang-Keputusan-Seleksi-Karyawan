<?php

namespace Database\Seeders;

use App\Models\Applicant;
use App\Models\Criteria;
use App\Models\CriteriaWeight;
use App\Models\Evaluation;
use App\Models\Evaluator;
use App\Models\SelectionPeriod;
use App\Models\SubCriteria;
use App\Models\User;
use App\Services\GroupDecisionAggregator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * DatabaseSeeder — Versi 2 (Naive Bayes-aware)
 *
 * Prinsip desain:
 *  1. Distribusi kelas dikontrol eksplisit: setiap periode punya sekitar
 *     40% pelamar "lemah" (skor rendah di kriteria berat) dan 60% "kuat",
 *     sehingga hasil TOPSIS menghasilkan campuran lulus/tidak_lulus yang
 *     wajar dan Naive Bayes mendapat training signal yang bermakna.
 *
 *  2. Evaluator punya bias opini berbeda (bukan sekadar ±1):
 *       HRD   → menilai tinggi: komunikasi & dokumen; rendah: teknis murni
 *       MGR   → menilai tinggi: pengalaman & troubleshoot; rendah: akademik formal
 *       DIR   → menilai tinggi: pendidikan & IPK; rendah: pengalaman operasional
 *     Delta per kriteria dikontrol per evaluator agar tetap di rentang [1,5].
 *
 *  3. Skor dirancang berkorelasi logis dengan profil pelamar:
 *       - S2 / IPK ≥ 3.75 → mendapat skor pendidikan 5
 *       - Pengalaman kerja relevan → skor pengalaman 4–5
 *       - Komunikatif / background public service → skor komunikasi tinggi
 *
 *  4. Semua periode menggunakan 7 kriteria dari set TU atau IT sehingga
 *     FEATURE_MAP di BayesPredictor dapat memetakan konsisten.
 *
 *  5. Jumlah pelamar 8–10 per periode (total ≥ 90) agar Bayes punya
 *     distribusi prior yang cukup saat training dengan 5 periode sebelumnya.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    // ------------------------------------------------------------------ //
    //  BIAS EVALUATOR — positif berarti "suka lebih", negatif "lebih kritis"
    //  Key = index kriteria dalam urutan periode (0-based)
    //  Nilai bisa berbeda per tipe periode (TU vs IT)
    // ------------------------------------------------------------------ //

    /**
     * Bias HRD per index kriteria untuk periode TU (TU1..TU7):
     *  TU2 (komunikasi) +1 | TU3 (dokumen) +1 | TU6 (pengalaman) 0 | TU7 (wawancara) +1
     *  TU4 (digital) -1 karena bukan ranah HRD.
     */
    private const BIAS_HRD_TU = [0, +1, +1, -1, 0, 0, +1];

    /**
     * Bias MGR per index kriteria untuk periode TU:
     *  TU5 (peraturan) +1 | TU6 (pengalaman) +1 | TU4 (digital) +1
     *  TU2 (komunikasi) -1 karena dianggap "sudah pasti".
     */
    private const BIAS_MGR_TU = [0, -1, 0, +1, +1, +1, 0];

    /**
     * Bias DIR per index kriteria untuk periode TU:
     *  TU1 (pendidikan) +1 | TU2 (komunikasi) +1
     *  TU6 (pengalaman) -1 karena direktur menilai potensi, bukan jam terbang.
     */
    private const BIAS_DIR_TU = [+1, +1, 0, 0, 0, -1, 0];

    /** Bias HRD untuk periode IT: IT4 (komunikasi teknis) +1, IT5 (simulasi) +1, IT2 (jaringan) -1 */
    private const BIAS_HRD_IT = [0, -1, 0, +1, +1, 0, 0];

    /** Bias MGR untuk periode IT: IT2 (jaringan) +1, IT5 (simulasi) +1, IT6 (pengalaman) +1, IT3 (IPK) -1 */
    private const BIAS_MGR_IT = [0, +1, -1, 0, +1, +1, 0];

    /** Bias DIR untuk periode IT: IT1 (pendidikan) +1, IT3 (IPK) +1, IT7 (logika) +1 */
    private const BIAS_DIR_IT = [+1, 0, +1, 0, 0, 0, +1];

    // ------------------------------------------------------------------ //

    public function run(): void
    {
        // ── USERS ───────────────────────────────────────────────────────
        User::create([
            'name'     => 'Administrator',
            'email'    => 'admin@spk.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        $direkturUser = User::create([
            'name'     => 'Direktur',
            'email'    => 'direktur@spk.com',
            'password' => bcrypt('password'),
            'role'     => 'direktur',
        ]);

        $hrdUser = User::create([
            'name'     => 'Kepala HRD',
            'email'    => 'hrd@spk.com',
            'password' => bcrypt('password'),
            'role'     => 'evaluator',
        ]);

        $managerUser = User::create([
            'name'     => 'Manager Unit',
            'email'    => 'manager@spk.com',
            'password' => bcrypt('password'),
            'role'     => 'evaluator',
        ]);

        Evaluator::where('code', 'default')->whereDoesntHave('evaluations')->delete();

        $hrdEvaluator = Evaluator::create([
            'code'       => 'HRD',
            'name'       => 'Divisi HRD',
            'role_label' => 'HRD',
            'user_id'    => $hrdUser->id,
            'sort_order' => 1,
            'is_active'  => true,
        ]);

        $managerEvaluator = Evaluator::create([
            'code'       => 'MGR',
            'name'       => 'Manager',
            'role_label' => 'Manager',
            'user_id'    => $managerUser->id,
            'sort_order' => 2,
            'is_active'  => true,
        ]);

        $direktorEvaluator = Evaluator::create([
            'code'       => 'DIR',
            'name'       => 'Direktur',
            'role_label' => 'Direktur',
            'user_id'    => $direkturUser->id,
            'sort_order' => 3,
            'is_active'  => true,
        ]);

        // ── KRITERIA ────────────────────────────────────────────────────
        foreach ($this->criteriaDefinitionsAdministrasiTu() as $def) {
            $this->createCriteriaWithSubs($def);
        }
        foreach ($this->criteriaDefinitionsTeknisIt() as $def) {
            $this->createCriteriaWithSubs($def);
        }

        // ── SHORTCUT ────────────────────────────────────────────────────
        $allEvaluators = collect([$hrdEvaluator, $managerEvaluator, $direktorEvaluator]);
        $hrdMgr        = collect([$hrdEvaluator, $managerEvaluator]);
        $hrdDir        = collect([$hrdEvaluator, $direktorEvaluator]);
        $mgrDir        = collect([$managerEvaluator, $direktorEvaluator]);

        // ================================================================
        // PERIODE 1 — Staff TU & Pendamping Akademik (9 pelamar, TU-type)
        // Target: 5 lulus (peringkat 1-5), 4 tidak lulus
        // Kekuatan mayoritas: TU2 (komunikasi) + TU3 (dokumen) + TU7 (wawancara)
        // ================================================================
        $p1 = SelectionPeriod::create([
            'name'        => 'Rekrutmen Staff TU & Pendamping Akademik — Sem. Gasal 2026',
            'position'    => 'Staff Tata Usaha / Pendamping Akademik',
            'start_date'  => '2025-08-10',
            'end_date'    => '2025-10-30',
            'description' => 'Penerimaan tenaga administrasi sekolah, layanan mahasiswa, dan pendamping program akademik. Penekanan pada komunikasi, kerapian dokumen, dan etos layanan.',
            'status'      => 'closed',
            'created_by'  => 1,
        ]);
        $p1->evaluators()->attach($allEvaluators->pluck('id')->toArray());
        $this->seedPeriodLinkedCriteriaRelative($p1, [
            'TU1' => 14, 'TU2' => 22, 'TU3' => 20, 'TU4' => 12, 'TU5' => 10, 'TU6' => 11, 'TU7' => 11,
        ]);
        // Kolom: TU1 TU2 TU3 TU4 TU5 TU6 TU7
        // Baris atas = calon kuat (lulus), baris bawah = calon lemah
        $scores1 = [
            // KUAT — komunikasi & dokumen & wawancara tinggi
            [5, 5, 5, 4, 4, 4, 5],  // Candradewi Kartika    — S2 Manajemen Pendidikan
            [4, 5, 4, 4, 4, 4, 5],  // Irfan Hakim           — S2 Manajemen, 5 thn kerja
            [4, 4, 5, 5, 4, 3, 4],  // Hesti Munawaroh       — S1 Pend.BI, teliti admin
            [4, 5, 4, 4, 4, 4, 4],  // Gilang Ramadhan       — S1 Ilmu Komunikasi
            [4, 4, 4, 4, 4, 4, 4],  // Ayu Rahmawati         — S1 Administrasi Publik
            // LEMAH — pendidikan kurang linear atau wawancara lemah
            [2, 3, 3, 3, 3, 2, 3],  // Bambang Sutrisno      — SMA saja, minim pengalaman
            [3, 2, 3, 2, 2, 2, 2],  // Citra Melani          — D3 Akuntansi, komunikasi lemah
            [3, 3, 2, 3, 2, 1, 2],  // Dodi Firmansyah       — D3 Teknik, tidak sesuai
            [2, 3, 3, 2, 3, 2, 3],  // Elis Nurjanah         — SMA, tidak ada pengalaman TU
        ];
        $applicants1 = [
            ['name' => 'Candradewi Kartika',   'phone' => '081111100001', 'gender' => 'P', 'birth_date' => '1995-04-12', 'education' => 'S2', 'major' => 'Manajemen Pendidikan',   'gpa' => 3.81, 'age' => 30, 'address' => 'Jl. Ciumbuleuit No. 88, Bandung'],
            ['name' => 'Irfan Hakim',           'phone' => '081111100002', 'gender' => 'L', 'birth_date' => '1993-12-01', 'education' => 'S2', 'major' => 'Manajemen',              'gpa' => 3.74, 'age' => 32, 'address' => 'Kompleks Setra Dago No. 12, Bandung'],
            ['name' => 'Hesti Munawaroh',       'phone' => '081111100003', 'gender' => 'P', 'birth_date' => '1999-07-05', 'education' => 'S1', 'major' => 'Pendidikan Bhs. Indonesia','gpa' => 3.67, 'age' => 26, 'address' => 'Jl. Perintis Kemerdekaan No. 3, Tasikmalaya'],
            ['name' => 'Gilang Ramadhan',       'phone' => '081111100004', 'gender' => 'L', 'birth_date' => '1998-02-18', 'education' => 'S1', 'major' => 'Ilmu Komunikasi',        'gpa' => 3.58, 'age' => 27, 'address' => 'Jl. Soekarno-Hatta No. 402, Bandung'],
            ['name' => 'Ayu Rahmawati',         'phone' => '081111100005', 'gender' => 'P', 'birth_date' => '1997-08-23', 'education' => 'S1', 'major' => 'Administrasi Publik',    'gpa' => 3.50, 'age' => 28, 'address' => 'Jl. Sukahaji No. 9, Bandung'],
            ['name' => 'Bambang Sutrisno',      'phone' => '081111100006', 'gender' => 'L', 'birth_date' => '2000-05-10', 'education' => 'SMA', 'major' => 'IPS',                   'gpa' => 0.00, 'age' => 25, 'address' => 'Jl. Raya Cileunyi No. 4, Bandung'],
            ['name' => 'Citra Melani',          'phone' => '081111100007', 'gender' => 'P', 'birth_date' => '2001-03-17', 'education' => 'D3', 'major' => 'Akuntansi',              'gpa' => 3.10, 'age' => 24, 'address' => 'Jl. Kopo No. 55, Bandung'],
            ['name' => 'Dodi Firmansyah',       'phone' => '081111100008', 'gender' => 'L', 'birth_date' => '2000-11-22', 'education' => 'D3', 'major' => 'Teknik Mesin',           'gpa' => 2.90, 'age' => 24, 'address' => 'Jl. Pajajaran No. 13, Bandung'],
            ['name' => 'Elis Nurjanah',         'phone' => '081111100009', 'gender' => 'P', 'birth_date' => '2002-01-30', 'education' => 'SMA', 'major' => 'IPA',                   'gpa' => 0.00, 'age' => 23, 'address' => 'Jl. Gedebage No. 7, Bandung'],
        ];
        $this->seedPeriodFull($p1, $applicants1, $scores1, $allEvaluators, 'TU');

        // ================================================================
        // PERIODE 2 — Teknisi Lab Komputer & IT Support (9 pelamar, IT-type)
        // Target: 5 lulus, 4 tidak lulus
        // Kekuatan: IT2 (jaringan) + IT3 (IPK) + IT5 (simulasi) + IT6 (pengalaman)
        // ================================================================
        $p2 = SelectionPeriod::create([
            'name'        => 'Rekrutmen Teknisi Lab Komputer & IT Support — 2025',
            'position'    => 'Teknisi Laboratorium / IT Support',
            'start_date'  => '2025-09-01',
            'end_date'    => '2025-11-15',
            'description' => 'Rekrutmen untuk perawatan perangkat lab, jaringan kampus ringan, dan dukungan teknis harian.',
            'status'      => 'closed',
            'created_by'  => 1,
        ]);
        $p2->evaluators()->attach($allEvaluators->pluck('id')->toArray());
        $this->seedPeriodLinkedCriteriaRelative($p2, [
            'IT1' => 12, 'IT2' => 22, 'IT3' => 21, 'IT4' => 9, 'IT5' => 14, 'IT6' => 17, 'IT7' => 5,
        ]);
        // Kolom: IT1 IT2 IT3 IT4 IT5 IT6 IT7
        $scores2 = [
            // KUAT
            [5, 5, 5, 4, 5, 4, 4],  // Putri Anggraini      — S2 Ilmu Komputer, IPK 3.91
            [4, 5, 5, 4, 4, 4, 5],  // Melati Sari Dewi     — S1 SI, IPK 3.88, aktif riset
            [4, 5, 4, 4, 4, 4, 4],  // Lutfi Andrean        — S1 TI, IPK 3.72, lab teknisi
            [4, 4, 4, 4, 5, 5, 3],  // Oka Setiawan         — S1 TI, 2 thn helpdesk
            [3, 4, 4, 4, 4, 4, 4],  // Qori Sandria         — S1 T.Elektro, troubleshoot oke
            // LEMAH
            [2, 2, 2, 2, 2, 1, 2],  // Restu Wijaya         — S1 TI IPK 2.95, pengalaman nol
            [2, 3, 3, 3, 3, 2, 2],  // Nanda Pratama        — D3 MI, IPK 3.19, tidak ada lab
            [1, 2, 2, 3, 2, 1, 2],  // Sandi Kurnia         — SMA, kursus komputer saja
            [2, 2, 3, 3, 2, 2, 2],  // Tika Wulandari       — S1 Manajemen, minim IT
        ];
        $applicants2 = [
            ['name' => 'Putri Anggraini',    'phone' => '081111200001', 'gender' => 'P', 'birth_date' => '1995-06-02', 'education' => 'S2', 'major' => 'Ilmu Komputer',       'gpa' => 3.91, 'age' => 30, 'address' => 'Jl. Solo Km 12, Kalasan, Sleman'],
            ['name' => 'Melati Sari Dewi',   'phone' => '081111200002', 'gender' => 'P', 'birth_date' => '1999-01-30', 'education' => 'S1', 'major' => 'Sistem Informasi',    'gpa' => 3.88, 'age' => 26, 'address' => 'Jl. Pandega Marta No. 8A, Sleman'],
            ['name' => 'Lutfi Andrean',       'phone' => '081111200003', 'gender' => 'L', 'birth_date' => '1998-08-22', 'education' => 'S1', 'major' => 'Teknik Informatika', 'gpa' => 3.72, 'age' => 27, 'address' => 'Jl. Kyai Mojo No. 14, Yogyakarta'],
            ['name' => 'Oka Setiawan',        'phone' => '081111200004', 'gender' => 'L', 'birth_date' => '1997-11-17', 'education' => 'S1', 'major' => 'Teknik Informatika', 'gpa' => 3.41, 'age' => 28, 'address' => 'Perum Graha Sewu Indah D/5, Yogyakarta'],
            ['name' => 'Qori Sandria Erlangga','phone' => '081111200005', 'gender' => 'L', 'birth_date' => '2000-10-25', 'education' => 'S1', 'major' => 'Teknik Elektro',     'gpa' => 3.36, 'age' => 25, 'address' => 'Jl. Babarsari No. 44, Condongcatur'],
            ['name' => 'Restu Wijaya',        'phone' => '081111200006', 'gender' => 'L', 'birth_date' => '2001-12-08', 'education' => 'S1', 'major' => 'Teknik Informatika', 'gpa' => 2.95, 'age' => 24, 'address' => 'Jl. Kaliurang Km 8,5, Sleman'],
            ['name' => 'Nanda Pratama',       'phone' => '081111200007', 'gender' => 'L', 'birth_date' => '2002-04-09', 'education' => 'D3', 'major' => 'Manajemen Informatika','gpa'=> 3.19, 'age' => 23, 'address' => 'Jl. Magelang Km 5, Yogyakarta'],
            ['name' => 'Sandi Kurnia',        'phone' => '081111200008', 'gender' => 'L', 'birth_date' => '2002-08-11', 'education' => 'SMA', 'major' => 'IPA',               'gpa' => 0.00, 'age' => 23, 'address' => 'Jl. Kaliurang Km 10, Sleman'],
            ['name' => 'Tika Wulandari',      'phone' => '081111200009', 'gender' => 'P', 'birth_date' => '2000-03-05', 'education' => 'S1', 'major' => 'Manajemen',          'gpa' => 3.25, 'age' => 25, 'address' => 'Jl. Colombo No. 5, Yogyakarta'],
        ];
        $this->seedPeriodFull($p2, $applicants2, $scores2, $allEvaluators, 'IT');

        // ================================================================
        // PERIODE 3 — Asisten Akademik (8 pelamar, TU-type)
        // Evaluator: HRD + MGR | Bobot: TU2 & TU3 dominan
        // ================================================================
        $p3 = SelectionPeriod::create([
            'name'        => 'Rekrutmen Asisten Akademik & Administrasi — 2025',
            'position'    => 'Asisten Akademik',
            'start_date'  => '2025-10-01',
            'end_date'    => '2025-12-15',
            'description' => 'Asisten administrasi akademik dengan fokus dukungan layanan mahasiswa dan persiapan dokumen.',
            'status'      => 'closed',
            'created_by'  => 1,
        ]);
        $p3->evaluators()->attach($hrdMgr->pluck('id')->toArray());
        $this->seedPeriodLinkedCriteriaRelative($p3, [
            'TU1' => 14, 'TU2' => 24, 'TU3' => 22, 'TU4' => 12, 'TU5' => 10, 'TU6' => 9, 'TU7' => 9,
        ]);
        $scores3 = [
            [5, 5, 5, 4, 4, 4, 4],  // Dewi Lestari         — S1 Pendidikan, IPK 3.71, layanan mahasiswa
            [4, 5, 5, 4, 4, 3, 5],  // Aditya Prabowo       — S1 Adm. Pendidikan, IPK 3.50
            [4, 5, 4, 4, 4, 4, 4],  // Bella Juwita         — S1 Manajemen, IPK 3.62
            [4, 4, 4, 5, 4, 3, 4],  // Eka Santoso          — S1 Ilmu Komunikasi, IPK 3.45
            [3, 3, 4, 3, 3, 3, 3],  // Cahya Putra          — D3 Adm Perkantoran, biasa
            [3, 2, 3, 3, 2, 2, 2],  // Farah Nabila         — D3 Sekretari, komunikasi kurang
            [2, 3, 2, 2, 2, 1, 2],  // Gunawan Adi          — SMA, tidak relevan
            [2, 2, 2, 3, 2, 1, 2],  // Hana Susanti         — D3 Akuntansi, tidak sesuai TU
        ];
        $applicants3 = $this->createApplicantProfiles('p03', [
            ['name' => 'Dewi Lestari',   'phone' => '081111300001', 'gender' => 'P', 'birth_date' => '1998-09-08', 'education' => 'S1',  'major' => 'Pendidikan',              'gpa' => 3.71, 'age' => 27, 'address' => 'Jl. Supratman No. 50, Bandung'],
            ['name' => 'Aditya Prabowo', 'phone' => '081111300002', 'gender' => 'L', 'birth_date' => '1996-11-12', 'education' => 'S1',  'major' => 'Administrasi Pendidikan', 'gpa' => 3.50, 'age' => 29, 'address' => 'Jl. Raya Cibiru No. 17, Bandung'],
            ['name' => 'Bella Juwita',   'phone' => '081111300003', 'gender' => 'P', 'birth_date' => '1997-06-03', 'education' => 'S1',  'major' => 'Manajemen',              'gpa' => 3.62, 'age' => 28, 'address' => 'Jl. Rancaekek No. 11, Bandung'],
            ['name' => 'Eka Santoso',    'phone' => '081111300004', 'gender' => 'L', 'birth_date' => '1994-07-18', 'education' => 'S1',  'major' => 'Ilmu Komunikasi',        'gpa' => 3.45, 'age' => 31, 'address' => 'Jl. Setiabudi No. 120, Bandung'],
            ['name' => 'Cahya Putra',    'phone' => '081111300005', 'gender' => 'L', 'birth_date' => '1995-12-28', 'education' => 'D3',  'major' => 'Adm. Perkantoran',       'gpa' => 3.28, 'age' => 30, 'address' => 'Jl. Cikutra No. 48, Bandung'],
            ['name' => 'Farah Nabila',   'phone' => '081111300006', 'gender' => 'P', 'birth_date' => '1999-02-25', 'education' => 'D3',  'major' => 'Sekretari',              'gpa' => 3.10, 'age' => 26, 'address' => 'Jl. Pajajaran No. 76, Bandung'],
            ['name' => 'Gunawan Adi',    'phone' => '081111300007', 'gender' => 'L', 'birth_date' => '2001-06-14', 'education' => 'SMA', 'major' => 'IPS',                    'gpa' => 0.00, 'age' => 24, 'address' => 'Jl. Pasir Kaliki No. 28, Bandung'],
            ['name' => 'Hana Susanti',   'phone' => '081111300008', 'gender' => 'P', 'birth_date' => '2001-10-31', 'education' => 'D3',  'major' => 'Akuntansi',              'gpa' => 3.05, 'age' => 24, 'address' => 'Jl. Gatot Subroto No. 45, Bandung'],
        ]);
        $this->seedPeriodFull($p3, $applicants3, $scores3, $hrdMgr, 'TU');

        // ================================================================
        // PERIODE 4 — Pengelola Dokumen Akademik (8 pelamar, TU-type)
        // Evaluator: HRD + DIR | Bobot: TU3 dominan, TU1 & TU2 seimbang
        // ================================================================
        $p4 = SelectionPeriod::create([
            'name'        => 'Rekrutmen Pengelola Dokumen Akademik — 2025',
            'position'    => 'Pengelola Dokumen Akademik',
            'start_date'  => '2025-11-05',
            'end_date'    => '2026-01-20',
            'description' => 'Pengelola arsip akademik dan dokumentasi surat menyurat untuk fakultas dan kampus.',
            'status'      => 'closed',
            'created_by'  => 1,
        ]);
        $p4->evaluators()->attach($hrdDir->pluck('id')->toArray());
        $this->seedPeriodLinkedCriteriaRelative($p4, [
            'TU1' => 18, 'TU2' => 18, 'TU3' => 24, 'TU4' => 12, 'TU5' => 11, 'TU6' => 9, 'TU7' => 8,
        ]);
        $scores4 = [
            [5, 4, 5, 4, 4, 4, 4],  // Galuh Maharani       — S2 Manajemen Pendidikan
            [4, 4, 5, 5, 4, 4, 3],  // Intan Pratiwi        — S1 Perpustakaan, sangat teliti
            [4, 5, 4, 4, 4, 3, 4],  // Hendra Wijaya        — S1 Adm Publik
            [4, 4, 4, 4, 4, 4, 4],  // Kartika Sari         — S1 Manajemen Informasi
            [3, 3, 3, 3, 3, 3, 3],  // Joko Santoso         — D3 Sekretari, biasa
            [2, 3, 2, 3, 2, 2, 3],  // Lukman Hakim         — S1 Hukum, kurang relevan
            [3, 2, 2, 2, 2, 1, 2],  // Mira Agustina        — SMA, tidak pengalaman arsip
            [2, 2, 3, 2, 2, 1, 2],  // Nurul Fadila         — D3 Desain, salah bidang
        ];
        $applicants4 = $this->createApplicantProfiles('p04', [
            ['name' => 'Galuh Maharani',  'phone' => '081111400001', 'gender' => 'P', 'birth_date' => '1996-03-22', 'education' => 'S2',  'major' => 'Manajemen Pendidikan', 'gpa' => 3.82, 'age' => 29, 'address' => 'Jl. Sukajadi No. 38, Bandung'],
            ['name' => 'Intan Pratiwi',   'phone' => '081111400002', 'gender' => 'P', 'birth_date' => '1998-01-19', 'education' => 'S1',  'major' => 'Perpustakaan',        'gpa' => 3.67, 'age' => 27, 'address' => 'Jl. Buahbatu No. 70, Bandung'],
            ['name' => 'Hendra Wijaya',   'phone' => '081111400003', 'gender' => 'L', 'birth_date' => '1995-08-11', 'education' => 'S1',  'major' => 'Administrasi Publik', 'gpa' => 3.55, 'age' => 30, 'address' => 'Jl. Cihampelas No. 22, Bandung'],
            ['name' => 'Kartika Sari',    'phone' => '081111400004', 'gender' => 'P', 'birth_date' => '1997-12-05', 'education' => 'S1',  'major' => 'Manajemen Informasi', 'gpa' => 3.74, 'age' => 28, 'address' => 'Jl. Pelajar Pejuang No. 100, Bandung'],
            ['name' => 'Joko Santoso',    'phone' => '081111400005', 'gender' => 'L', 'birth_date' => '1994-10-30', 'education' => 'D3',  'major' => 'Sekretari',           'gpa' => 3.25, 'age' => 31, 'address' => 'Jl. Juanda No. 29, Bandung'],
            ['name' => 'Lukman Hakim',    'phone' => '081111400006', 'gender' => 'L', 'birth_date' => '1995-05-21', 'education' => 'S1',  'major' => 'Hukum',               'gpa' => 3.40, 'age' => 30, 'address' => 'Jl. Cipaganti No. 12, Bandung'],
            ['name' => 'Mira Agustina',   'phone' => '081111400007', 'gender' => 'P', 'birth_date' => '2003-04-18', 'education' => 'SMA', 'major' => 'IPA',                 'gpa' => 0.00, 'age' => 22, 'address' => 'Jl. Raya Ujung Berung No. 5, Bandung'],
            ['name' => 'Nurul Fadila',    'phone' => '081111400008', 'gender' => 'P', 'birth_date' => '2002-09-07', 'education' => 'D3',  'major' => 'Desain Komunikasi',   'gpa' => 3.15, 'age' => 23, 'address' => 'Jl. Karapitan No. 33, Bandung'],
        ]);
        $this->seedPeriodFull($p4, $applicants4, $scores4, $hrdDir, 'TU');

        // ================================================================
        // PERIODE 5 — Operator Lab Komputer (9 pelamar, IT-type)
        // Evaluator: semua | Bobot: IT2 & IT5 dominan
        // ================================================================
        $p5 = SelectionPeriod::create([
            'name'        => 'Rekrutmen Operator Lab Komputer — 2026',
            'position'    => 'Operator Lab Komputer',
            'start_date'  => '2026-01-15',
            'end_date'    => '2026-03-31',
            'description' => 'Operator lab yang mengelola workstation, software dasar, dan dukungan teknis mahasiswa.',
            'status'      => 'closed',
            'created_by'  => 1,
        ]);
        $p5->evaluators()->attach($allEvaluators->pluck('id')->toArray());
        $this->seedPeriodLinkedCriteriaRelative($p5, [
            'IT1' => 12, 'IT2' => 25, 'IT3' => 17, 'IT4' => 10, 'IT5' => 18, 'IT6' => 10, 'IT7' => 8,
        ]);
        // Kolom: IT1 IT2 IT3 IT4 IT5 IT6 IT7
        $scores5 = [
            [4, 5, 5, 4, 5, 4, 4],  // Maya Anindya         — S1 TI IPK 3.68, kuasai lab
            [4, 5, 4, 4, 5, 5, 4],  // Rafael Hutama        — S1 T.Komputer, 2 thn lab
            [4, 5, 4, 3, 4, 4, 4],  // Naufal Ramadhan      — S1 SI IPK 3.54
            [4, 4, 4, 4, 4, 4, 3],  // Putra Waluyo         — S1 MI IPK 3.33
            [3, 4, 4, 4, 4, 3, 4],  // Qila Saputri         — S1 T.Elektro IPK 3.50
            [2, 3, 3, 3, 3, 2, 3],  // Rani Kusuma          — D3 Adm Perkantoran, salah bidang
            [2, 2, 3, 3, 2, 1, 2],  // Surya Adi            — SMA, kursus komputer saja
            [3, 2, 2, 3, 2, 2, 2],  // Tania Wibowo         — S1 Manajemen, IT minim
            [1, 2, 2, 3, 2, 1, 1],  // Udin Saputra         — D3 Keuangan, tidak relevan
        ];
        $applicants5 = $this->createApplicantProfiles('p05', [
            ['name' => 'Maya Anindya',   'phone' => '081111500001', 'gender' => 'P', 'birth_date' => '1997-02-27', 'education' => 'S1',  'major' => 'Teknik Informatika', 'gpa' => 3.68, 'age' => 29, 'address' => 'Jl. Ganesha No. 24, Bandung'],
            ['name' => 'Rafael Hutama',  'phone' => '081111500002', 'gender' => 'L', 'birth_date' => '1996-03-05', 'education' => 'S1',  'major' => 'Teknik Komputer',    'gpa' => 3.41, 'age' => 30, 'address' => 'Jl. Ciumbuleuit No. 30, Bandung'],
            ['name' => 'Naufal Ramadhan','phone' => '081111500003', 'gender' => 'L', 'birth_date' => '1996-08-01', 'education' => 'S1',  'major' => 'Sistem Informasi',   'gpa' => 3.54, 'age' => 30, 'address' => 'Jl. Dipati Ukur No. 3, Bandung'],
            ['name' => 'Putra Waluyo',   'phone' => '081111500004', 'gender' => 'L', 'birth_date' => '1998-04-04', 'education' => 'S1',  'major' => 'Manajemen Informatika','gpa'=> 3.33, 'age' => 28, 'address' => 'Jl. Riau No. 56, Bandung'],
            ['name' => 'Qila Saputri',   'phone' => '081111500005', 'gender' => 'P', 'birth_date' => '1997-11-14', 'education' => 'S1',  'major' => 'Teknik Elektro',     'gpa' => 3.50, 'age' => 29, 'address' => 'Jl. Suci No. 19, Bandung'],
            ['name' => 'Rani Kusuma',    'phone' => '081111500006', 'gender' => 'P', 'birth_date' => '2000-06-25', 'education' => 'D3',  'major' => 'Adm. Perkantoran',   'gpa' => 3.10, 'age' => 26, 'address' => 'Jl. Situ Saeur No. 5, Bandung'],
            ['name' => 'Surya Adi',      'phone' => '081111500007', 'gender' => 'L', 'birth_date' => '2002-09-11', 'education' => 'SMA', 'major' => 'IPA',                'gpa' => 0.00, 'age' => 24, 'address' => 'Jl. Gedebage No. 22, Bandung'],
            ['name' => 'Tania Wibowo',   'phone' => '081111500008', 'gender' => 'P', 'birth_date' => '1999-12-01', 'education' => 'S1',  'major' => 'Manajemen',          'gpa' => 3.20, 'age' => 27, 'address' => 'Jl. Lengkong No. 11, Bandung'],
            ['name' => 'Udin Saputra',   'phone' => '081111500009', 'gender' => 'L', 'birth_date' => '2001-07-17', 'education' => 'D3',  'major' => 'Keuangan',           'gpa' => 2.95, 'age' => 25, 'address' => 'Jl. Moh. Toha No. 88, Bandung'],
        ]);
        $this->seedPeriodFull($p5, $applicants5, $scores5, $allEvaluators, 'IT');

        // ================================================================
        // PERIODE 6 — Helpdesk IT (8 pelamar, IT-type)
        // Evaluator: HRD + MGR | Bobot: IT2 & IT4 (komunikasi) dominan
        // ================================================================
        $p6 = SelectionPeriod::create([
            'name'        => 'Rekrutmen Helpdesk IT — 2026',
            'position'    => 'Helpdesk IT',
            'start_date'  => '2026-02-20',
            'end_date'    => '2026-04-30',
            'description' => 'Helpdesk IT yang melayani troubleshooting pengguna internal dan dukungan teknis aplikasi kampus.',
            'status'      => 'closed',
            'created_by'  => 1,
        ]);
        $p6->evaluators()->attach($hrdMgr->pluck('id')->toArray());
        $this->seedPeriodLinkedCriteriaRelative($p6, [
            'IT1' => 10, 'IT2' => 20, 'IT3' => 15, 'IT4' => 20, 'IT5' => 15, 'IT6' => 12, 'IT7' => 8,
        ]);
        $scores6 = [
            [4, 5, 4, 5, 4, 4, 3],  // Salsa Rahma          — S1 SI, komunikatif & jaringan
            [4, 5, 4, 5, 4, 3, 3],  // Wulan Septiani       — S1 MI, sangat komunikatif
            [4, 4, 4, 4, 4, 4, 4],  // Timothy Yudha        — S1 TI, pengalaman helpdesk
            [3, 4, 3, 4, 4, 4, 4],  // Vino Aditya          — S1 T.Elektro, oke helpdesk
            [3, 3, 3, 3, 3, 2, 3],  // Xavier Pratama       — S1 T.Komputer, biasa
            [2, 3, 3, 2, 3, 2, 2],  // Yudi Hermawan        — D3 MI, komunikasi lemah
            [2, 2, 2, 2, 2, 1, 2],  // Zulfa Nora           — SMA, tidak teknis
            [3, 2, 3, 2, 2, 1, 2],  // Arifin Ilham         — S1 Hukum, salah bidang
        ];
        $applicants6 = $this->createApplicantProfiles('p06', [
            ['name' => 'Salsa Rahma',    'phone' => '081111600001', 'gender' => 'P', 'birth_date' => '1998-07-01', 'education' => 'S1',  'major' => 'Sistem Informasi',   'gpa' => 3.65, 'age' => 28, 'address' => 'Jl. Ir. Juanda No. 103, Bandung'],
            ['name' => 'Wulan Septiani', 'phone' => '081111600002', 'gender' => 'P', 'birth_date' => '1998-03-03', 'education' => 'S1',  'major' => 'Manajemen Informatika','gpa'=> 3.60, 'age' => 28, 'address' => 'Jl. Dago No. 8, Bandung'],
            ['name' => 'Timothy Yudha',  'phone' => '081111600003', 'gender' => 'L', 'birth_date' => '1996-09-18', 'education' => 'S1',  'major' => 'Teknik Informatika', 'gpa' => 3.58, 'age' => 30, 'address' => 'Jl. Sukahaji No. 12, Bandung'],
            ['name' => 'Vino Aditya',    'phone' => '081111600004', 'gender' => 'L', 'birth_date' => '1997-10-15', 'education' => 'S1',  'major' => 'Teknik Elektro',     'gpa' => 3.35, 'age' => 29, 'address' => 'Jl. Cihampelas No. 65, Bandung'],
            ['name' => 'Xavier Pratama', 'phone' => '081111600005', 'gender' => 'L', 'birth_date' => '1995-11-22', 'education' => 'S1',  'major' => 'Teknik Komputer',    'gpa' => 3.44, 'age' => 31, 'address' => 'Jl. Merdeka No. 44, Bandung'],
            ['name' => 'Yudi Hermawan',  'phone' => '081111600006', 'gender' => 'L', 'birth_date' => '2000-02-08', 'education' => 'D3',  'major' => 'Manajemen Informatika','gpa'=> 3.12, 'age' => 26, 'address' => 'Jl. Karangsari No. 18, Bandung'],
            ['name' => 'Zulfa Nora',     'phone' => '081111600007', 'gender' => 'P', 'birth_date' => '2003-05-14', 'education' => 'SMA', 'major' => 'IPS',                'gpa' => 0.00, 'age' => 23, 'address' => 'Jl. Cibeunying No. 3, Bandung'],
            ['name' => 'Arifin Ilham',   'phone' => '081111600008', 'gender' => 'L', 'birth_date' => '1998-08-09', 'education' => 'S1',  'major' => 'Hukum',              'gpa' => 3.30, 'age' => 28, 'address' => 'Jl. Pasundan No. 7, Bandung'],
        ]);
        $this->seedPeriodFull($p6, $applicants6, $scores6, $hrdMgr, 'IT');

        // ================================================================
        // PERIODE 7 — Pengelola Arsip Digital (9 pelamar, TU-type)
        // Evaluator: MGR + DIR | Bobot: TU3 & TU4 dominan
        // ================================================================
        $p7 = SelectionPeriod::create([
            'name'        => 'Rekrutmen Pengelola Arsip Digital — 2026',
            'position'    => 'Pengelola Arsip Digital',
            'start_date'  => '2026-03-10',
            'end_date'    => '2026-05-31',
            'description' => 'Pengelola arsip digital dan sistem dokumen internal perguruan.',
            'status'      => 'closed',
            'created_by'  => 1,
        ]);
        $p7->evaluators()->attach($mgrDir->pluck('id')->toArray());
        $this->seedPeriodLinkedCriteriaRelative($p7, [
            'TU1' => 14, 'TU2' => 14, 'TU3' => 22, 'TU4' => 20, 'TU5' => 14, 'TU6' => 8, 'TU7' => 8,
        ]);
        $scores7 = [
            [4, 4, 5, 5, 4, 4, 4],  // Zahra Fathia         — S1 Manajemen Informasi, digital
            [5, 4, 5, 4, 4, 3, 4],  // Chandra Wirawan      — S2 Ilmu Perpustakaan
            [4, 4, 4, 5, 4, 4, 3],  // Bella Nur            — S1 TI, sistem arsip digital
            [4, 4, 4, 4, 4, 4, 4],  // Agus Suharto         — S1 Adm Publik, pengalaman
            [3, 3, 3, 3, 3, 3, 3],  // Evan Pratama         — S1 Manajemen, standar
            [2, 3, 2, 3, 2, 2, 3],  // Dina Maharani        — D3 Sekretari, kurang digital
            [3, 2, 2, 2, 2, 1, 2],  // Bambang Eko          — SMA, tidak relevan
            [2, 2, 3, 2, 2, 1, 2],  // Cici Rahayu          — D3 Akuntansi, tidak cocok
            [1, 3, 2, 2, 2, 1, 2],  // Darmanto             — SMA, tidak relevan
        ];
        $applicants7 = $this->createApplicantProfiles('p07', [
            ['name' => 'Zahra Fathia',    'phone' => '081111700001', 'gender' => 'P', 'birth_date' => '1997-04-21', 'education' => 'S1',  'major' => 'Manajemen Informasi', 'gpa' => 3.55, 'age' => 29, 'address' => 'Jl. Buah Batu No. 21, Bandung'],
            ['name' => 'Chandra Wirawan', 'phone' => '081111700002', 'gender' => 'L', 'birth_date' => '1994-11-09', 'education' => 'S2',  'major' => 'Ilmu Perpustakaan',   'gpa' => 3.80, 'age' => 32, 'address' => 'Jl. Kebon Kawung No. 141, Bandung'],
            ['name' => 'Bella Nur',        'phone' => '081111700003', 'gender' => 'P', 'birth_date' => '1999-12-02', 'education' => 'S1',  'major' => 'Teknik Informatika',  'gpa' => 3.42, 'age' => 27, 'address' => 'Jl. Karang Tengah No. 7, Bandung'],
            ['name' => 'Agus Suharto',    'phone' => '081111700004', 'gender' => 'L', 'birth_date' => '1996-06-18', 'education' => 'S1',  'major' => 'Administrasi Publik', 'gpa' => 3.49, 'age' => 30, 'address' => 'Jl. Cicaheum No. 19, Bandung'],
            ['name' => 'Evan Pratama',    'phone' => '081111700005', 'gender' => 'L', 'birth_date' => '1997-02-13', 'education' => 'S1',  'major' => 'Manajemen',           'gpa' => 3.48, 'age' => 29, 'address' => 'Jl. Samanhudi No. 18, Bandung'],
            ['name' => 'Dina Maharani',   'phone' => '081111700006', 'gender' => 'P', 'birth_date' => '1998-08-14', 'education' => 'D3',  'major' => 'Sekretari',           'gpa' => 3.20, 'age' => 28, 'address' => 'Jl. Pahlawan No. 32, Bandung'],
            ['name' => 'Bambang Eko',     'phone' => '081111700007', 'gender' => 'L', 'birth_date' => '2001-03-09', 'education' => 'SMA', 'major' => 'IPS',                 'gpa' => 0.00, 'age' => 25, 'address' => 'Jl. Cimindi No. 14, Bandung'],
            ['name' => 'Cici Rahayu',     'phone' => '081111700008', 'gender' => 'P', 'birth_date' => '2001-07-27', 'education' => 'D3',  'major' => 'Akuntansi',           'gpa' => 3.08, 'age' => 25, 'address' => 'Jl. Margahayu No. 5, Bandung'],
            ['name' => 'Darmanto',        'phone' => '081111700009', 'gender' => 'L', 'birth_date' => '2003-01-15', 'education' => 'SMA', 'major' => 'IPA',                 'gpa' => 0.00, 'age' => 23, 'address' => 'Jl. Cibaduyut No. 18, Bandung'],
        ]);
        $this->seedPeriodFull($p7, $applicants7, $scores7, $mgrDir, 'TU');

        // ================================================================
        // PERIODE 8 — Koordinator Layanan Mahasiswa (9 pelamar, TU-type)
        // Evaluator: semua | Bobot: TU2 & TU7 (komunikasi & wawancara) dominan
        // ================================================================
        $p8 = SelectionPeriod::create([
            'name'        => 'Rekrutmen Koordinator Layanan Mahasiswa — 2026',
            'position'    => 'Koordinator Layanan Mahasiswa',
            'start_date'  => '2026-04-01',
            'end_date'    => '2026-06-30',
            'description' => 'Koordinator layanan mahasiswa untuk manajemen antrian, komunikasi, dan dukungan akademik.',
            'status'      => 'open',
            'created_by'  => 1,
        ]);
        $p8->evaluators()->attach($allEvaluators->pluck('id')->toArray());
        $this->seedPeriodLinkedCriteriaRelative($p8, [
            'TU1' => 13, 'TU2' => 22, 'TU3' => 16, 'TU4' => 11, 'TU5' => 10, 'TU6' => 11, 'TU7' => 17,
        ]);
        $scores8 = [
            [4, 5, 4, 4, 4, 4, 5],  // Irene Damayanti      — S1 Ilmu Komunikasi, service
            [4, 5, 5, 4, 4, 4, 4],  // Gita Rahmi           — S1 Pendidikan, IPK 3.72
            [3, 4, 4, 4, 4, 3, 5],  // Hadi Saputra         — S1 Psikologi, empati tinggi
            [4, 4, 4, 4, 4, 4, 4],  // Jamal Fauzi          — S1 Adm Publik, koordinasi
            [3, 4, 3, 4, 3, 4, 4],  // Fahriandi            — S1 Manajemen, oke layanan
            [3, 3, 3, 3, 3, 3, 3],  // Kirana Putri         — D3 Sekretari, standar
            [2, 2, 3, 2, 2, 2, 2],  // Leni Hartati         — SMA, tidak relevan
            [2, 3, 2, 3, 2, 1, 3],  // Muhamad Rizki        — D3 TI, salah bidang
            [3, 2, 2, 2, 2, 1, 2],  // Nining Suryani       — D3 Akuntansi, tidak cocok
        ];
        $applicants8 = $this->createApplicantProfiles('p08', [
            ['name' => 'Irene Damayanti', 'phone' => '081111800001', 'gender' => 'P', 'birth_date' => '1998-06-29', 'education' => 'S1',  'major' => 'Ilmu Komunikasi',   'gpa' => 3.68, 'age' => 28, 'address' => 'Jl. Siliwangi No. 14, Bandung'],
            ['name' => 'Gita Rahmi',      'phone' => '081111800002', 'gender' => 'P', 'birth_date' => '1997-11-20', 'education' => 'S1',  'major' => 'Pendidikan',        'gpa' => 3.72, 'age' => 29, 'address' => 'Jl. Ciumbuleuit No. 90, Bandung'],
            ['name' => 'Hadi Saputra',    'phone' => '081111800003', 'gender' => 'L', 'birth_date' => '1995-03-05', 'education' => 'S1',  'major' => 'Psikologi',         'gpa' => 3.51, 'age' => 31, 'address' => 'Jl. Sultan Agung No. 24, Bandung'],
            ['name' => 'Jamal Fauzi',     'phone' => '081111800004', 'gender' => 'L', 'birth_date' => '1997-09-12', 'education' => 'S1',  'major' => 'Administrasi Publik','gpa' => 3.47, 'age' => 29, 'address' => 'Jl. Cikapayang No. 11, Bandung'],
            ['name' => 'Fahriandi',       'phone' => '081111800005', 'gender' => 'L', 'birth_date' => '1996-04-14', 'education' => 'S1',  'major' => 'Manajemen',         'gpa' => 3.59, 'age' => 30, 'address' => 'Jl. Pasir Kaliki No. 60, Bandung'],
            ['name' => 'Kirana Putri',    'phone' => '081111800006', 'gender' => 'P', 'birth_date' => '1999-01-05', 'education' => 'D3',  'major' => 'Sekretari',         'gpa' => 3.33, 'age' => 27, 'address' => 'Jl. Pajajaran No. 88, Bandung'],
            ['name' => 'Leni Hartati',    'phone' => '081111800007', 'gender' => 'P', 'birth_date' => '2002-11-18', 'education' => 'SMA', 'major' => 'IPS',               'gpa' => 0.00, 'age' => 24, 'address' => 'Jl. Ciwastra No. 9, Bandung'],
            ['name' => 'Muhamad Rizki',   'phone' => '081111800008', 'gender' => 'L', 'birth_date' => '2001-06-23', 'education' => 'D3',  'major' => 'Teknik Informatika','gpa' => 3.15, 'age' => 25, 'address' => 'Jl. Peta No. 22, Bandung'],
            ['name' => 'Nining Suryani',  'phone' => '081111800009', 'gender' => 'P', 'birth_date' => '2000-08-30', 'education' => 'D3',  'major' => 'Akuntansi',         'gpa' => 3.05, 'age' => 26, 'address' => 'Jl. Kopo Sayati No. 4, Bandung'],
        ]);
        $this->seedPeriodFull($p8, $applicants8, $scores8, $allEvaluators, 'TU');

        // ================================================================
        // PERIODE 9 — Analis Data Akademik (8 pelamar, IT-type)
        // Evaluator: HRD + DIR | Bobot: IT3 (IPK) & IT5 dominan
        // ================================================================
        $p9 = SelectionPeriod::create([
            'name'        => 'Rekrutmen Analis Data Akademik — 2026',
            'position'    => 'Analis Data Akademik',
            'start_date'  => '2026-05-10',
            'end_date'    => '2026-07-25',
            'description' => 'Analis data akademik yang menyiapkan laporan kinerja mahasiswa dan hasil evaluasi internal.',
            'status'      => 'open',
            'created_by'  => 1,
        ]);
        $p9->evaluators()->attach($hrdDir->pluck('id')->toArray());
        $this->seedPeriodLinkedCriteriaRelative($p9, [
            'IT1' => 16, 'IT2' => 12, 'IT3' => 26, 'IT4' => 10, 'IT5' => 18, 'IT6' => 10, 'IT7' => 8,
        ]);
        $scores9 = [
            [4, 4, 5, 3, 5, 3, 4],  // Omar Farhan          — S2 Teknik Industri, IPK 3.83
            [4, 4, 5, 3, 5, 3, 3],  // Lina Kurnia          — S1 Statistika, IPK 3.75
            [4, 4, 5, 4, 4, 3, 3],  // Nadia Putri          — S1 TI, IPK 3.69, analitik
            [3, 4, 4, 4, 4, 4, 3],  // Miko Adi             — S1 SI, pengalaman data
            [3, 3, 4, 4, 4, 3, 3],  // Putri Wulandari      — S1 Manajemen, data analisis
            [2, 3, 3, 3, 3, 2, 3],  // Rian Hidayat         — S1 TI, IPK 3.52, biasa
            [2, 2, 2, 3, 2, 2, 2],  // Sinta Dewi           — D3 Komputer, tidak analitik
            [1, 2, 2, 3, 2, 1, 2],  // Toni Rahardjo        — SMA, tidak relevan
        ];
        $applicants9 = $this->createApplicantProfiles('p09', [
            ['name' => 'Omar Farhan',      'phone' => '081111900001', 'gender' => 'L', 'birth_date' => '1995-12-11', 'education' => 'S2',  'major' => 'Teknik Industri',    'gpa' => 3.83, 'age' => 31, 'address' => 'Jl. Ciumbuleuit No. 16, Bandung'],
            ['name' => 'Lina Kurnia',      'phone' => '081111900002', 'gender' => 'P', 'birth_date' => '1997-08-07', 'education' => 'S1',  'major' => 'Statistika',         'gpa' => 3.75, 'age' => 29, 'address' => 'Jl. Sejuk No. 49, Bandung'],
            ['name' => 'Nadia Putri',      'phone' => '081111900003', 'gender' => 'P', 'birth_date' => '1998-05-24', 'education' => 'S1',  'major' => 'Teknik Informatika', 'gpa' => 3.69, 'age' => 28, 'address' => 'Jl. Lembong No. 33, Bandung'],
            ['name' => 'Miko Adi',         'phone' => '081111900004', 'gender' => 'L', 'birth_date' => '1996-04-18', 'education' => 'S1',  'major' => 'Sistem Informasi',   'gpa' => 3.58, 'age' => 30, 'address' => 'Jl. Ahmad Yani No. 67, Bandung'],
            ['name' => 'Putri Wulandari',  'phone' => '081111900005', 'gender' => 'P', 'birth_date' => '1997-02-02', 'education' => 'S1',  'major' => 'Manajemen',          'gpa' => 3.66, 'age' => 29, 'address' => 'Jl. Ir. H. Juanda No. 20, Bandung'],
            ['name' => 'Rian Hidayat',     'phone' => '081111900006', 'gender' => 'L', 'birth_date' => '1996-07-27', 'education' => 'S1',  'major' => 'Teknik Informatika', 'gpa' => 3.52, 'age' => 30, 'address' => 'Jl. Siliwangi No. 76, Bandung'],
            ['name' => 'Sinta Dewi',       'phone' => '081111900007', 'gender' => 'P', 'birth_date' => '2001-03-14', 'education' => 'D3',  'major' => 'Teknik Komputer',    'gpa' => 3.10, 'age' => 25, 'address' => 'Jl. Cisangkuy No. 19, Bandung'],
            ['name' => 'Toni Rahardjo',    'phone' => '081111900008', 'gender' => 'L', 'birth_date' => '2002-10-22', 'education' => 'SMA', 'major' => 'IPA',                'gpa' => 0.00, 'age' => 24, 'address' => 'Jl. Kiaracondong No. 44, Bandung'],
        ]);
        $this->seedPeriodFull($p9, $applicants9, $scores9, $hrdDir, 'IT');

        // ================================================================
        // PERIODE 10 — Pengelola Infrastruktur Jaringan (8 pelamar, IT-type)
        // Evaluator: MGR + DIR | Bobot: IT2 & IT6 dominan
        // TARGET PERIODE PREDIKSI — masih "open", digunakan Bayes
        // ================================================================
        $p10 = SelectionPeriod::create([
            'name'        => 'Rekrutmen Pengelola Infrastruktur Jaringan — 2026',
            'position'    => 'Pengelola Infrastruktur Jaringan',
            'start_date'  => '2026-06-01',
            'end_date'    => '2026-08-31',
            'description' => 'Teknisi jaringan untuk pengelolaan LAN/Wi-Fi dan pemeliharaan infrastruktur kampus.',
            'status'      => 'open',
            'created_by'  => 1,
        ]);
        $p10->evaluators()->attach($mgrDir->pluck('id')->toArray());
        $this->seedPeriodLinkedCriteriaRelative($p10, [
            'IT1' => 10, 'IT2' => 26, 'IT3' => 15, 'IT4' => 9, 'IT5' => 12, 'IT6' => 20, 'IT7' => 8,
        ]);
        $scores10 = [
            [4, 5, 4, 3, 4, 5, 4],  // Vian Maulana         — S1 Jaringan Komputer, berpengalaman
            [4, 5, 4, 4, 4, 4, 3],  // Winda Rahman         — S1 TI, jaringan baik
            [3, 4, 4, 3, 4, 4, 4],  // Umi Kalsum           — S1 T.Komputer, troubleshoot
            [4, 4, 3, 3, 3, 5, 4],  // Sari Amelia          — S1 T.Elektro, infra
            [3, 3, 3, 3, 3, 3, 3],  // Tegar Pratama        — S1 TI, standar
            [2, 3, 3, 3, 3, 2, 2],  // Xena Darma           — D3 T.Komputer, kurang pengalaman
            [2, 2, 2, 2, 2, 1, 2],  // Yohanes Bagus        — SMA, tidak relevan
            [1, 2, 2, 3, 2, 1, 1],  // Zaenal Arifin        — D3 Keuangan, salah bidang
        ];
        $applicants10 = $this->createApplicantProfiles('p10', [
            ['name' => 'Vian Maulana',   'phone' => '081112000001', 'gender' => 'L', 'birth_date' => '1995-05-29', 'education' => 'S1',  'major' => 'Jaringan Komputer',  'gpa' => 3.38, 'age' => 31, 'address' => 'Jl. Dago No. 45, Bandung'],
            ['name' => 'Winda Rahman',   'phone' => '081112000002', 'gender' => 'P', 'birth_date' => '1997-07-21', 'education' => 'S1',  'major' => 'Teknik Informatika', 'gpa' => 3.61, 'age' => 29, 'address' => 'Jl. Setiabudi No. 88, Bandung'],
            ['name' => 'Umi Kalsum',     'phone' => '081112000003', 'gender' => 'P', 'birth_date' => '1998-02-16', 'education' => 'S1',  'major' => 'Teknik Komputer',    'gpa' => 3.44, 'age' => 28, 'address' => 'Jl. Sukajadi No. 19, Bandung'],
            ['name' => 'Sari Amelia',    'phone' => '081112000004', 'gender' => 'P', 'birth_date' => '1997-11-25', 'education' => 'S1',  'major' => 'Teknik Elektro',     'gpa' => 3.57, 'age' => 29, 'address' => 'Jl. Pelajar Pejuang No. 92, Bandung'],
            ['name' => 'Tegar Pratama',  'phone' => '081112000005', 'gender' => 'L', 'birth_date' => '1996-12-05', 'education' => 'S1',  'major' => 'Teknik Informatika', 'gpa' => 3.49, 'age' => 30, 'address' => 'Jl. Cihampelas No. 40, Bandung'],
            ['name' => 'Xena Darma',     'phone' => '081112000006', 'gender' => 'P', 'birth_date' => '1998-10-09', 'education' => 'D3',  'major' => 'Teknik Komputer',    'gpa' => 3.26, 'age' => 28, 'address' => 'Jl. Kebon Kelapa No. 32, Bandung'],
            ['name' => 'Yohanes Bagus',  'phone' => '081112000007', 'gender' => 'L', 'birth_date' => '2002-04-03', 'education' => 'SMA', 'major' => 'IPA',                'gpa' => 0.00, 'age' => 24, 'address' => 'Jl. Terusan Pasteur No. 11, Bandung'],
            ['name' => 'Zaenal Arifin',  'phone' => '081112000008', 'gender' => 'L', 'birth_date' => '2001-09-17', 'education' => 'D3',  'major' => 'Keuangan',           'gpa' => 2.88, 'age' => 25, 'address' => 'Jl. Cibiru No. 8, Bandung'],
        ]);
        $this->seedPeriodFull($p10, $applicants10, $scores10, $mgrDir, 'IT');

        // ── AGREGASI ────────────────────────────────────────────────────
        $aggregator = app(GroupDecisionAggregator::class);
        foreach (SelectionPeriod::all() as $p) {
            $aggregator->rebuild((int) $p->id, 'average');
        }

        $this->command?->info('Seeder v2 selesai: 10 periode, distribusi lulus/tidak_lulus terkontrol, bias evaluator realistis, siap Naive Bayes.');
    }

    // ================================================================
    //  SEED HELPERS
    // ================================================================

    /**
     * Seed satu periode lengkap: buat applicant, lalu buat evaluasi
     * dengan bias per-evaluator yang realistis sesuai tipe periode (TU/IT).
     *
     * @param  Collection<int,Evaluator>  $evaluators
     * @param  'TU'|'IT'                 $type
     */
    private function seedPeriodFull(
        SelectionPeriod $period,
        array $applicantsData,
        array $scoreMatrix,
        Collection $evaluators,
        string $type
    ): void {
        $createdApplicants = [];
        foreach ($applicantsData as $a) {
            $createdApplicants[] = Applicant::create(array_merge(['period_id' => $period->id], $a));
        }

        $criteriaModels = $period->linkedCriteria()->orderByPivot('sort_order')->get();

        // Petakan code evaluator → bias array
        $biasMap = $this->resolveBiasMap($evaluators, $type);

        foreach (array_values($createdApplicants) as $appIdx => $applicant) {
            foreach ($criteriaModels as $cIdx => $criteriaModel) {
                $base = $scoreMatrix[$appIdx][$cIdx] ?? 3;
                foreach ($evaluators->values() as $evaluatorRow) {
                    $biasArr = $biasMap[$evaluatorRow->code] ?? array_fill(0, 7, 0);
                    $delta   = $biasArr[$cIdx] ?? 0;
                    // Tambahkan sedikit variasi acak kecil agar tidak terlalu mekanis
                    // tapi HANYA pada skor menengah (bukan ekstrem 1 atau 5) agar pattern tetap terjaga
                    $noise = ($base >= 2 && $base <= 4) ? (rand(0, 1) === 1 ? 0 : (rand(0, 1) === 1 ? 1 : -1)) : 0;
                    // Noise maksimal ±1, dan hanya 1/3 kemungkinan non-zero
                    $noise = (rand(1, 3) === 1) ? $noise : 0;
                    $scoreVal = max(1, min(5, $base + $delta + $noise));
                    Evaluation::create([
                        'period_id'    => $period->id,
                        'applicant_id' => $applicant->id,
                        'criteria_id'  => $criteriaModel->id,
                        'evaluator_id' => $evaluatorRow->id,
                        'score'        => $scoreVal,
                    ]);
                }
            }
        }
    }

    /**
     * Kembalikan mapping code evaluator → array bias per index kriteria.
     *
     * @param  Collection<int,Evaluator>  $evaluators
     * @param  'TU'|'IT'                 $type
     * @return array<string, array<int,int>>
     */
    private function resolveBiasMap(Collection $evaluators, string $type): array
    {
        $tuMap = [
            'HRD' => self::BIAS_HRD_TU,
            'MGR' => self::BIAS_MGR_TU,
            'DIR' => self::BIAS_DIR_TU,
        ];
        $itMap = [
            'HRD' => self::BIAS_HRD_IT,
            'MGR' => self::BIAS_MGR_IT,
            'DIR' => self::BIAS_DIR_IT,
        ];
        $sourceMap = $type === 'IT' ? $itMap : $tuMap;

        $result = [];
        foreach ($evaluators as $e) {
            $result[$e->code] = $sourceMap[$e->code] ?? array_fill(0, 7, 0);
        }
        return $result;
    }

    /**
     * @param  array<string, float|int>  $relativeWeightsByCriterionCode
     */
    private function seedPeriodLinkedCriteriaRelative(SelectionPeriod $period, array $relativeWeightsByCriterionCode): void
    {
        $sum = array_sum($relativeWeightsByCriterionCode);
        if ($sum <= 0) {
            throw new \InvalidArgumentException('Total bobot relatif kriteria harus > 0.');
        }

        $syncData = [];
        $order    = 0;
        foreach ($relativeWeightsByCriterionCode as $code => $_rel) {
            $criteria              = Criteria::where('code', $code)->firstOrFail();
            $syncData[$criteria->id] = ['sort_order' => $order++];
        }
        $period->linkedCriteria()->sync($syncData);

        CriteriaWeight::where('period_id', $period->id)->delete();
        foreach ($relativeWeightsByCriterionCode as $code => $rel) {
            $criteria = Criteria::where('code', $code)->firstOrFail();
            CriteriaWeight::create([
                'period_id'   => $period->id,
                'criteria_id' => $criteria->id,
                'weight'      => round(((float) $rel) / $sum, 6),
            ]);
        }
    }

    private function createApplicantProfiles(string $slug, array $profiles): array
    {
        return array_map(function (array $profile, int $idx) use ($slug): array {
            $localPart = strtolower(preg_replace('/[^a-z0-9]+/', '.', $profile['name']));
            return array_merge($profile, [
                'email' => sprintf('%s.%s.%02d@mail.id', $localPart, $slug, $idx + 1),
            ]);
        }, $profiles, array_keys($profiles));
    }

    // ================================================================
    //  DEFINISI KRITERIA — tidak berubah dari v1
    // ================================================================

    private function criteriaDefinitionsAdministrasiTu(): array
    {
        return [
            [
                'code' => 'TU1', 'name' => 'Pendidikan formal & kesesuaian bidang dengan tugas TU/akademik',
                'description' => 'Kelulusan terakhir dan relevansi disiplin ilmu untuk administrasi perguruan.',
                'type' => 'benefit', 'importance' => 10,
                'sub' => [
                    ['name' => 'Tidak ada / tidak sesuai dengan administrasi atau pendidikan', 'value' => 1],
                    ['name' => 'SMK/SMA (kurang linear dengan TU akademik tinggi)', 'value' => 2],
                    ['name' => 'D3 administrasi/sekretariat/akuntansi/perkantoran', 'value' => 3],
                    ['name' => 'S1 administrasi pub., komunikasi, pendidikan, hukum perguruan', 'value' => 4],
                    ['name' => 'S2/S3 atau S1 jurusan sangat selaras dengan administrasi akademik', 'value' => 5],
                ],
            ],
            [
                'code' => 'TU2', 'name' => 'Komunikasi interpersonal & kemampuan melayani stakeholder',
                'description' => 'Kemampuan komunikasi sopan kepada mahasiswa, orang tua/wali, atau dosen.',
                'type' => 'benefit', 'importance' => 9,
                'sub' => [
                    ['name' => 'Sangat kaku atau sulit menyampaikan secara sopan', 'value' => 1],
                    ['name' => 'Kurang jelas atau sering salah paham', 'value' => 2],
                    ['name' => 'Standar sopan santun kantor perguruan', 'value' => 3],
                    ['name' => 'Menenangkan situasi konflik kecil secara profesional', 'value' => 4],
                    ['name' => 'Sangat empatik, jelas, dan konsisten melayani', 'value' => 5],
                ],
            ],
            [
                'code' => 'TU3', 'name' => 'Ketepatan dokumen akademik & tata penyimpanan berkas fisik/digital',
                'description' => 'Kemampuan memeriksa KRS, ijazah, transkrip, dan arsip akademik secara rapi.',
                'type' => 'benefit', 'importance' => 8,
                'sub' => [
                    ['name' => 'Banyak salah ketik/format sesuai aturan akademik', 'value' => 1],
                    ['name' => 'Sesekali salah label berkas atau data entri', 'value' => 2],
                    ['name' => 'Konsisten rapi tetapi perlu banyak pengawasan detail', 'value' => 3],
                    ['name' => 'Hampir bebas salah dan mengikuti SOP dokumentasi mandiri', 'value' => 4],
                    ['name' => 'Sangat teliti dapat melakukan quality check untuk tim lain', 'value' => 5],
                ],
            ],
            [
                'code' => 'TU4', 'name' => 'Penguasaan aplikasi perkantoran (spreadsheet, formulir daring, LMS ringan)',
                'description' => 'Penguasaan paket office atau sistem administrasi perguruan sederhana.',
                'type' => 'benefit', 'importance' => 7,
                'sub' => [
                    ['name' => 'Hanya menguasai mengetik dokumen sangat minimal', 'value' => 1],
                    ['name' => 'Bisa spreadsheet tetapi formula dasar tidak lancar', 'value' => 2],
                    ['name' => 'Menguasai tabel spreadsheet & mail merge formulir mahasiswa', 'value' => 3],
                    ['name' => 'Bisa bikin rekapitulasi & dashboard laporan sederhana', 'value' => 4],
                    ['name' => 'Mahir otomasi formulir/skrip ringan serta adaptasi LMS cepat', 'value' => 5],
                ],
            ],
            [
                'code' => 'TU5', 'name' => 'Pemahaman peraturan akademik internal & etika penyimpanan data',
                'description' => 'Memahami alur akademik perguruan, kerahasiaan data mahasiswa.',
                'type' => 'benefit', 'importance' => 6,
                'sub' => [
                    ['name' => 'Tidak memahami SOP akademik perguruan', 'value' => 1],
                    ['name' => 'Tahu sebagian tetapi perlu penyuluhan rutin tiap tugas baru', 'value' => 2],
                    ['name' => 'Bisa jalankan tugas sesuai SOP dokumentasi akademik reguler', 'value' => 3],
                    ['name' => 'Menguasai SOP serta dapat sosialisasi ke unit lain', 'value' => 4],
                    ['name' => 'Andal sebagai second opinion kepatuhan data mahasiswa', 'value' => 5],
                ],
            ],
            [
                'code' => 'TU6', 'name' => 'Pengalaman kerja di administrasi perguruan atau lembaga pelayanan akademik',
                'description' => 'Riwayat praktik di TU fakultas, kampus, akademi, pusat bahasa, dll.',
                'type' => 'benefit', 'importance' => 5,
                'sub' => [
                    ['name' => 'Belum pernah mendukung proses akademik secara formal', 'value' => 1],
                    ['name' => 'Magang part-time sekurang-kurangnya 3 bulan TU serupa', 'value' => 2],
                    ['name' => '1–12 bulan TU/sekretariat perguruan penuh-time', 'value' => 3],
                    ['name' => '1–3 tahun lapangan dokumentasi akademik berkesinambungan', 'value' => 4],
                    ['name' => 'Lebih dari 3 tahun senioritas berkas akademik perguruan', 'value' => 5],
                ],
            ],
            [
                'code' => 'TU7', 'name' => 'Wawancara perilaku profesionalisme, integritas layanan & budaya kerja',
                'description' => 'Konsistensi jawaban tentang etos kerja, penanganan komplain, dan fleksibilitas.',
                'type' => 'benefit', 'importance' => 8,
                'sub' => [
                    ['name' => 'Jawaban defensif/tidak menunjukkan komitmen layanan', 'value' => 1],
                    ['name' => 'Umum saja sehingga sulit menilai integritas', 'value' => 2],
                    ['name' => 'Menunjukkan pemahaman budaya layanan perguruan standar', 'value' => 3],
                    ['name' => 'Contoh nyata menangani kasus pelanggan internal', 'value' => 4],
                    ['name' => 'Sangat matang, reflektif, dan adaptif terhadap institusi', 'value' => 5],
                ],
            ],
        ];
    }

    private function criteriaDefinitionsTeknisIt(): array
    {
        return [
            [
                'code' => 'IT1', 'name' => 'Relevansi gelar & dasar pendidikan teknologi informasi',
                'description' => 'Sejauh mana riwayat studi mendukung troubleshooting perangkat keras/lunak.',
                'type' => 'benefit', 'importance' => 9,
                'sub' => [
                    ['name' => 'Jurusan tidak berkaitan dengan TI / belum pernah kursus teknis', 'value' => 1],
                    ['name' => 'Jurusan campuran (elektro/hukum) dengan kursus TI dasar', 'value' => 2],
                    ['name' => 'D3/S1 MI/SI dengan praktikum jaringan atau pemrograman ringan', 'value' => 3],
                    ['name' => 'S1 TI/RPL dengan praktik lab hardware & software terstruktur', 'value' => 4],
                    ['name' => 'S2 Ilmu Komputer/TI atau sertifikasi industri relevan (mis. CCNA)', 'value' => 5],
                ],
            ],
            [
                'code' => 'IT2', 'name' => 'Pengetahuan praktis jaringan kabel/nirkabel & sistem operasi workstation',
                'description' => 'Kemampuan konfigurasi DHCP statis, VLAN sederhana, imaging OS lab.',
                'type' => 'benefit', 'importance' => 10,
                'sub' => [
                    ['name' => 'Hanya tahu pengguna akhir umum (browsing & office)', 'value' => 1],
                    ['name' => 'Bisa instal driver dasar tetapi tidak pernah urus topologi jaringan', 'value' => 2],
                    ['name' => 'Bisa troubleshooting Wi-Fi lokal & recovery windows standar', 'value' => 3],
                    ['name' => 'Mampu kelola switch managed entry-level & server file share', 'value' => 4],
                    ['name' => 'Menguasai imaging massal lab, VLAN guest, dan monitoring resource', 'value' => 5],
                ],
            ],
            [
                'code' => 'IT3', 'name' => 'Rekam jejak akademik kognitif (IPK) untuk substansi teknis',
                'description' => 'Indikator komitmen belajar materi abstrak yang diasosiasikan dengan kinerja teknisi junior.',
                'type' => 'benefit', 'importance' => 8,
                'sub' => [
                    ['name' => 'IPK < 2.50 atau banyak mata kuliah ulang kritikal TI', 'value' => 1],
                    ['name' => 'IPK 2.50 – 2.89', 'value' => 2],
                    ['name' => 'IPK 3.00 – 3.34', 'value' => 3],
                    ['name' => 'IPK 3.35 – 3.74 dengan bukti aktivitas ekstrakurikuler teknis', 'value' => 4],
                    ['name' => 'IPK ≥ 3.75 atau penyerta proyek teknologi publikasi', 'value' => 5],
                ],
            ],
            [
                'code' => 'IT4', 'name' => 'Komunikasi teknis ke pengguna awam & dokumentasi tiket dukungan',
                'description' => 'Menjelaskan solusi secara non-jargon dan mencatat log intervensi konsisten.',
                'type' => 'benefit', 'importance' => 6,
                'sub' => [
                    ['name' => 'Suka menyalahkan pengguna & tidak dokumentasikan apa pun', 'value' => 1],
                    ['name' => 'Catatan dukungan tersebar atau sulit dibaca rekannya', 'value' => 2],
                    ['name' => 'Bisa dokumentasi standar SOP ticketing institusional', 'value' => 3],
                    ['name' => 'Bahasa sederhana sehingga dosen cepat mengerti workaround', 'value' => 4],
                    ['name' => 'Panduan self-help singkat bagi pengguna bisa diproduksi mandiri', 'value' => 5],
                ],
            ],
            [
                'code' => 'IT5', 'name' => 'Wawancara simulasi kasus troubleshooting mendesak di laboratorium',
                'description' => 'Pemikiran terstruktur atas kasus gagal booting massal atau jaringan lab mati mendadak.',
                'type' => 'benefit', 'importance' => 9,
                'sub' => [
                    ['name' => 'Tanpa struktur penyelidikan (random coba)', 'value' => 1],
                    ['name' => 'Menyebut gejala tetapi gagal menyusun prioritas penyebab', 'value' => 2],
                    ['name' => 'Mengikuti checklist standar penyelidikan lab', 'value' => 3],
                    ['name' => 'Beradaptasi secara iteratif serta mengkomunikasikan trade-off', 'value' => 4],
                    ['name' => 'Menunjukkan pattern recognition masalah rekuren kampus', 'value' => 5],
                ],
            ],
            [
                'code' => 'IT6', 'name' => 'Pengalaman support IT / maintenance lab komputer atau helpdesk kampus',
                'description' => 'Durasi serta kedalaman menghadapi SLA perbaikan workstation laboratorium perguruan.',
                'type' => 'benefit', 'importance' => 8,
                'sub' => [
                    ['name' => 'Tanpa pengalaman formil support/perawatan workstation publik', 'value' => 1],
                    ['name' => 'Pernah membantu satu project maintenance singkat tidak berkelanjutan', 'value' => 2],
                    ['name' => 'Minimal 6 bulan helpdesk perguruan full-time', 'value' => 3],
                    ['name' => '1–2 tahun dukungan workstation lab akademik banyak unit', 'value' => 4],
                    ['name' => 'Lebih dari 2 tahun memimpin aktivasi lab semesteran massal', 'value' => 5],
                ],
            ],
            [
                'code' => 'IT7', 'name' => 'Penalaran logika & tes teknis cepat pola riset operasional sederhana',
                'description' => 'Tes numerik pola, klasifikasi penyebab kegagalan, atau mini coding pseudocode.',
                'type' => 'benefit', 'importance' => 7,
                'sub' => [
                    ['name' => 'Skor tes logika/teknis di bawah standar kelulusan organisasi', 'value' => 1],
                    ['name' => 'Hanya memenuhi ambang kelulusan tetapi lambat secara waktu', 'value' => 2],
                    ['name' => 'Memenuhi target waktu serta akurasi rata median', 'value' => 3],
                    ['name' => 'Akurasi di atas median dengan komunikasi cara berpikir jelas', 'value' => 4],
                    ['name' => 'Outstanding: cepat serta menemukan pola tidak biasa dalam tes', 'value' => 5],
                ],
            ],
        ];
    }

    /** @phpstan-param array{code: string, name: string, type: string, importance: int, description?: ?string, sub: list<array{name: string, value: positive-int}>} $def */
    private function createCriteriaWithSubs(array $def): void
    {
        $subs = $def['sub'];
        unset($def['sub']);
        if (! isset($def['description'])) {
            $def['description'] = null;
        }
        $criteria = Criteria::create($def);
        foreach ($subs as $sub) {
            SubCriteria::create([
                'criteria_id' => $criteria->id,
                'name'        => $sub['name'],
                'value'       => $sub['value'],
            ]);
        }
    }
}
