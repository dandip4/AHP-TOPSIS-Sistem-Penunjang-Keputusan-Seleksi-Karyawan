<?php

namespace Database\Seeders;

use App\Models\Applicant;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\SelectionPeriod;
use App\Models\SubCriteria;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@spk.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Direktur',
            'email' => 'direktur@spk.com',
            'password' => bcrypt('password'),
            'role' => 'direktur',
        ]);

        $criteria = [
            ['code' => 'C1', 'name' => 'Pendidikan Terakhir', 'type' => 'benefit', 'importance' => 9],
            ['code' => 'C2', 'name' => 'Usia', 'type' => 'benefit', 'importance' => 8],
            ['code' => 'C3', 'name' => 'IPK', 'type' => 'benefit', 'importance' => 6],
            ['code' => 'C4', 'name' => 'Kemampuan Bahasa Asing', 'type' => 'benefit', 'importance' => 5],
            ['code' => 'C5', 'name' => 'Wawancara', 'type' => 'benefit', 'importance' => 4],
            ['code' => 'C6', 'name' => 'Pengalaman Kerja', 'type' => 'benefit', 'importance' => 3],
            ['code' => 'C7', 'name' => 'Psikotest', 'type' => 'benefit', 'importance' => 2],
        ];

        foreach ($criteria as $c) {
            Criteria::create($c);
        }

        $subCriteria = [
            'C1' => [
                ['name' => 'SMA/SMK', 'value' => 1],
                ['name' => 'D3', 'value' => 2],
                ['name' => 'S1', 'value' => 3],
                ['name' => 'S2', 'value' => 4],
                ['name' => 'S3', 'value' => 5],
            ],
            'C2' => [
                ['name' => '< 20 tahun', 'value' => 1],
                ['name' => '20-25 tahun', 'value' => 2],
                ['name' => '26-30 tahun', 'value' => 3],
                ['name' => '31-35 tahun', 'value' => 4],
                ['name' => '> 35 tahun', 'value' => 5],
            ],
            'C3' => [
                ['name' => '< 2.50', 'value' => 1],
                ['name' => '2.50 - 2.99', 'value' => 2],
                ['name' => '3.00 - 3.49', 'value' => 3],
                ['name' => '3.50 - 3.74', 'value' => 4],
                ['name' => '3.75 - 4.00', 'value' => 5],
            ],
            'C4' => [
                ['name' => 'Tidak Ada', 'value' => 1],
                ['name' => 'Pasif', 'value' => 2],
                ['name' => 'Cukup', 'value' => 3],
                ['name' => 'Aktif', 'value' => 4],
                ['name' => 'Sangat Aktif', 'value' => 5],
            ],
            'C5' => [
                ['name' => 'Sangat Kurang', 'value' => 1],
                ['name' => 'Kurang', 'value' => 2],
                ['name' => 'Cukup', 'value' => 3],
                ['name' => 'Baik', 'value' => 4],
                ['name' => 'Sangat Baik', 'value' => 5],
            ],
            'C6' => [
                ['name' => 'Tidak Ada', 'value' => 1],
                ['name' => '< 1 tahun', 'value' => 2],
                ['name' => '1-2 tahun', 'value' => 3],
                ['name' => '3-5 tahun', 'value' => 4],
                ['name' => '> 5 tahun', 'value' => 5],
            ],
            'C7' => [
                ['name' => 'Sangat Kurang', 'value' => 1],
                ['name' => 'Kurang', 'value' => 2],
                ['name' => 'Cukup', 'value' => 3],
                ['name' => 'Baik', 'value' => 4],
                ['name' => 'Sangat Baik', 'value' => 5],
            ],
        ];

        foreach ($subCriteria as $code => $subs) {
            $parent = Criteria::where('code', $code)->first();
            foreach ($subs as $sub) {
                SubCriteria::create([
                    'criteria_id' => $parent->id,
                    'name' => $sub['name'],
                    'value' => $sub['value'],
                ]);
            }
        }

        // === Periode Seleksi Dummy ===
        $period = SelectionPeriod::create([
            'name' => 'Seleksi Karyawan Batch 1 - 2026',
            'position' => 'Staff Administrasi & Pengajar',
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-30',
            'description' => 'Penerimaan karyawan baru periode April 2026 untuk posisi staff administrasi dan pengajar.',
            'status' => 'open',
            'created_by' => 1,
        ]);

        // === 10 Pelamar Dummy (sesuai jurnal) ===
        $applicants = [
            ['name' => 'Aldefa Pratiwi',      'email' => 'aldefa@mail.com',      'phone' => '081234567801', 'gender' => 'P', 'birth_date' => '1998-03-15', 'education' => 'S1', 'major' => 'Sistem Informasi',      'gpa' => 3.85, 'age' => 28, 'address' => 'Jl. Merdeka No. 10, Medan'],
            ['name' => 'Novela Andriyani',    'email' => 'novela@mail.com',      'phone' => '081234567802', 'gender' => 'P', 'birth_date' => '1997-07-22', 'education' => 'S1', 'major' => 'Teknik Informatika',    'gpa' => 3.60, 'age' => 29, 'address' => 'Jl. Sudirman No. 25, Medan'],
            ['name' => 'Muhammad Izzu Salam', 'email' => 'izzu@mail.com',        'phone' => '081234567803', 'gender' => 'L', 'birth_date' => '2000-01-10', 'education' => 'D3', 'major' => 'Manajemen Informatika', 'gpa' => 3.10, 'age' => 26, 'address' => 'Jl. Gatot Subroto No. 5, Medan'],
            ['name' => 'Hadi Atmaja',         'email' => 'hadi@mail.com',        'phone' => '081234567804', 'gender' => 'L', 'birth_date' => '1996-11-05', 'education' => 'S1', 'major' => 'Akuntansi',             'gpa' => 3.72, 'age' => 30, 'address' => 'Jl. Diponegoro No. 18, Medan'],
            ['name' => 'Nita Permata Sari',   'email' => 'nita@mail.com',        'phone' => '081234567805', 'gender' => 'P', 'birth_date' => '1999-05-28', 'education' => 'D3', 'major' => 'Administrasi Bisnis',   'gpa' => 3.20, 'age' => 27, 'address' => 'Jl. Ahmad Yani No. 30, Medan'],
            ['name' => 'Fikri Akbar Pratama', 'email' => 'fikri@mail.com',       'phone' => '081234567806', 'gender' => 'L', 'birth_date' => '1995-09-12', 'education' => 'S1', 'major' => 'Teknik Informatika',    'gpa' => 3.45, 'age' => 31, 'address' => 'Jl. Imam Bonjol No. 7, Medan'],
            ['name' => 'Nabila Syifa',        'email' => 'nabila@mail.com',      'phone' => '081234567807', 'gender' => 'P', 'birth_date' => '2001-02-17', 'education' => 'S1', 'major' => 'Pendidikan Bahasa Arab', 'gpa' => 3.30, 'age' => 25, 'address' => 'Jl. Sisingamangaraja No. 12, Medan'],
            ['name' => 'Syafira Ayu',         'email' => 'syafira@mail.com',     'phone' => '081234567808', 'gender' => 'P', 'birth_date' => '1998-08-03', 'education' => 'D3', 'major' => 'Bahasa Inggris',        'gpa' => 3.78, 'age' => 28, 'address' => 'Jl. Krakatau No. 22, Medan'],
            ['name' => 'Muhammad Rifqi',      'email' => 'rifqi@mail.com',       'phone' => '081234567809', 'gender' => 'L', 'birth_date' => '1997-12-20', 'education' => 'S1', 'major' => 'Sistem Informasi',      'gpa' => 3.90, 'age' => 29, 'address' => 'Jl. Veteran No. 15, Medan'],
            ['name' => 'Dewa Abid',           'email' => 'dewa@mail.com',        'phone' => '081234567810', 'gender' => 'L', 'birth_date' => '1996-06-08', 'education' => 'S1', 'major' => 'Teknik Komputer',       'gpa' => 3.55, 'age' => 30, 'address' => 'Jl. Pemuda No. 33, Medan'],
        ];

        $createdApplicants = [];
        foreach ($applicants as $a) {
            $createdApplicants[] = Applicant::create([
                'period_id' => $period->id,
                ...$a,
            ]);
        }

        // === Penilaian Dummy (sesuai Tabel 7 jurnal) ===
        // Urutan kriteria: C1, C2, C3, C4, C5, C6, C7
        $scores = [
            [5, 5, 5, 4, 5, 3, 5], // Aldefa Pratiwi
            [5, 4, 5, 3, 4, 3, 3], // Novela Andriyani
            [3, 5, 3, 3, 4, 4, 2], // Muhammad Izzu Salam
            [5, 4, 5, 3, 4, 3, 5], // Hadi Atmaja
            [3, 3, 5, 3, 2, 3, 4], // Nita Permata Sari
            [5, 5, 3, 3, 3, 4, 3], // Fikri Akbar Pratama
            [5, 3, 2, 4, 4, 4, 3], // Nabila Syifa
            [3, 5, 5, 4, 5, 4, 4], // Syafira Ayu
            [5, 4, 5, 4, 5, 3, 3], // Muhammad Rifqi
            [5, 5, 5, 4, 3, 3, 3], // Dewa Abid
        ];

        $criteriaModels = Criteria::orderBy('code')->get();

        foreach ($createdApplicants as $idx => $applicant) {
            foreach ($criteriaModels as $cIdx => $criteriaModel) {
                Evaluation::create([
                    'period_id' => $period->id,
                    'applicant_id' => $applicant->id,
                    'criteria_id' => $criteriaModel->id,
                    'score' => $scores[$idx][$cIdx],
                ]);
            }
        }

        // ================================================================
        // PERIODE 2: Seleksi Batch 2 - 7 Pelamar
        // ================================================================
        $period2 = SelectionPeriod::create([
            'name' => 'Seleksi Karyawan Batch 2 - 2026',
            'position' => 'Staff IT & Programmer',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'description' => 'Penerimaan karyawan baru periode Juni 2026 untuk posisi staff IT dan programmer.',
            'status' => 'closed',
            'created_by' => 1,
        ]);

        $applicants2 = [
            ['name' => 'Raka Aditya Putra',  'email' => 'raka@mail.com',    'phone' => '081345678901', 'gender' => 'L', 'birth_date' => '1999-02-14', 'education' => 'S1', 'major' => 'Teknik Informatika',    'gpa' => 3.75, 'age' => 27, 'address' => 'Jl. Pahlawan No. 5, Jakarta'],
            ['name' => 'Siti Nurhaliza',      'email' => 'siti.n@mail.com',  'phone' => '081345678902', 'gender' => 'P', 'birth_date' => '1998-09-20', 'education' => 'S1', 'major' => 'Sistem Informasi',      'gpa' => 3.88, 'age' => 28, 'address' => 'Jl. Kenanga No. 12, Jakarta'],
            ['name' => 'Budi Santoso',        'email' => 'budi.s@mail.com',  'phone' => '081345678903', 'gender' => 'L', 'birth_date' => '1997-04-08', 'education' => 'D3', 'major' => 'Manajemen Informatika', 'gpa' => 3.20, 'age' => 29, 'address' => 'Jl. Melati No. 8, Jakarta'],
            ['name' => 'Anisa Rahma',         'email' => 'anisa.r@mail.com', 'phone' => '081345678904', 'gender' => 'P', 'birth_date' => '2000-12-03', 'education' => 'S1', 'major' => 'Ilmu Komputer',         'gpa' => 3.65, 'age' => 26, 'address' => 'Jl. Dahlia No. 20, Jakarta'],
            ['name' => 'Fajar Kurniawan',     'email' => 'fajar.k@mail.com', 'phone' => '081345678905', 'gender' => 'L', 'birth_date' => '1996-06-17', 'education' => 'S2', 'major' => 'Teknik Komputer',       'gpa' => 3.92, 'age' => 30, 'address' => 'Jl. Mawar No. 3, Jakarta'],
            ['name' => 'Dewi Lestari',        'email' => 'dewi.l@mail.com',  'phone' => '081345678906', 'gender' => 'P', 'birth_date' => '2001-01-25', 'education' => 'S1', 'major' => 'Pendidikan Matematika', 'gpa' => 3.40, 'age' => 25, 'address' => 'Jl. Anggrek No. 15, Jakarta'],
            ['name' => 'Arif Rahman Hakim',   'email' => 'arif.r@mail.com',  'phone' => '081345678907', 'gender' => 'L', 'birth_date' => '1998-08-11', 'education' => 'S1', 'major' => 'Teknik Elektro',        'gpa' => 3.55, 'age' => 28, 'address' => 'Jl. Flamboyan No. 9, Jakarta'],
        ];

        $createdApplicants2 = [];
        foreach ($applicants2 as $a) {
            $createdApplicants2[] = Applicant::create([
                'period_id' => $period2->id,
                ...$a,
            ]);
        }

        $scores2 = [
            [5, 3, 5, 4, 4, 3, 4], // Raka
            [5, 4, 5, 5, 5, 3, 5], // Siti
            [3, 4, 3, 2, 3, 4, 3], // Budi
            [5, 3, 4, 4, 4, 2, 4], // Anisa
            [5, 5, 5, 5, 4, 5, 5], // Fajar
            [5, 3, 3, 3, 3, 2, 3], // Dewi
            [5, 4, 4, 3, 3, 4, 3], // Arif
        ];

        foreach ($createdApplicants2 as $idx => $applicant) {
            foreach ($criteriaModels as $cIdx => $criteriaModel) {
                Evaluation::create([
                    'period_id' => $period2->id,
                    'applicant_id' => $applicant->id,
                    'criteria_id' => $criteriaModel->id,
                    'score' => $scores2[$idx][$cIdx],
                ]);
            }
        }

        // ================================================================
        // PERIODE 3: Seleksi Batch 3 - 13 Pelamar
        // ================================================================
        $period3 = SelectionPeriod::create([
            'name' => 'Seleksi Karyawan Batch 3 - 2026',
            'position' => 'Staff Marketing & Customer Service',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'description' => 'Penerimaan karyawan baru periode September 2026 untuk posisi staff marketing dan customer service.',
            'status' => 'open',
            'created_by' => 1,
        ]);

        $applicants3 = [
            ['name' => 'Putri Amelia',         'email' => 'putri.a@mail.com',   'phone' => '081456789001', 'gender' => 'P', 'birth_date' => '1999-05-10', 'education' => 'S1', 'major' => 'Manajemen',              'gpa' => 3.70, 'age' => 27, 'address' => 'Jl. Cempaka No. 1, Surabaya'],
            ['name' => 'Rizky Maulana',        'email' => 'rizky.m@mail.com',   'phone' => '081456789002', 'gender' => 'L', 'birth_date' => '1997-11-22', 'education' => 'S1', 'major' => 'Komunikasi',             'gpa' => 3.50, 'age' => 29, 'address' => 'Jl. Teratai No. 6, Surabaya'],
            ['name' => 'Dina Maharani',        'email' => 'dina.m@mail.com',    'phone' => '081456789003', 'gender' => 'P', 'birth_date' => '2000-03-18', 'education' => 'D3', 'major' => 'Administrasi Bisnis',    'gpa' => 3.25, 'age' => 26, 'address' => 'Jl. Tulip No. 14, Surabaya'],
            ['name' => 'Ahmad Fauzi',          'email' => 'ahmad.f@mail.com',   'phone' => '081456789004', 'gender' => 'L', 'birth_date' => '1996-08-05', 'education' => 'S1', 'major' => 'Teknik Industri',        'gpa' => 3.80, 'age' => 30, 'address' => 'Jl. Sakura No. 22, Surabaya'],
            ['name' => 'Lina Oktaviani',       'email' => 'lina.o@mail.com',    'phone' => '081456789005', 'gender' => 'P', 'birth_date' => '1998-12-30', 'education' => 'S1', 'major' => 'Psikologi',              'gpa' => 3.60, 'age' => 28, 'address' => 'Jl. Bougenville No. 7, Surabaya'],
            ['name' => 'Yoga Pratama',         'email' => 'yoga.p@mail.com',    'phone' => '081456789006', 'gender' => 'L', 'birth_date' => '2001-07-14', 'education' => 'S1', 'major' => 'Manajemen Pemasaran',    'gpa' => 3.35, 'age' => 25, 'address' => 'Jl. Lavender No. 19, Surabaya'],
            ['name' => 'Mega Puspita',         'email' => 'mega.p@mail.com',    'phone' => '081456789007', 'gender' => 'P', 'birth_date' => '1999-01-09', 'education' => 'D3', 'major' => 'Sekretari',              'gpa' => 3.15, 'age' => 27, 'address' => 'Jl. Kamboja No. 25, Surabaya'],
            ['name' => 'Ilham Saputra',        'email' => 'ilham.s@mail.com',   'phone' => '081456789008', 'gender' => 'L', 'birth_date' => '1997-04-26', 'education' => 'S2', 'major' => 'Manajemen',              'gpa' => 3.90, 'age' => 29, 'address' => 'Jl. Seruni No. 11, Surabaya'],
            ['name' => 'Ratna Sari Dewi',      'email' => 'ratna.s@mail.com',   'phone' => '081456789009', 'gender' => 'P', 'birth_date' => '2000-10-15', 'education' => 'S1', 'major' => 'Hubungan Internasional', 'gpa' => 3.68, 'age' => 26, 'address' => 'Jl. Edelweiss No. 4, Surabaya'],
            ['name' => 'Bayu Aji Nugroho',     'email' => 'bayu.a@mail.com',    'phone' => '081456789010', 'gender' => 'L', 'birth_date' => '1998-06-02', 'education' => 'S1', 'major' => 'Teknik Informatika',     'gpa' => 3.45, 'age' => 28, 'address' => 'Jl. Kemuning No. 16, Surabaya'],
            ['name' => 'Citra Permatasari',    'email' => 'citra.p@mail.com',   'phone' => '081456789011', 'gender' => 'P', 'birth_date' => '2001-02-28', 'education' => 'S1', 'major' => 'Akuntansi',              'gpa' => 3.55, 'age' => 25, 'address' => 'Jl. Jasmine No. 30, Surabaya'],
            ['name' => 'Galih Wicaksono',      'email' => 'galih.w@mail.com',   'phone' => '081456789012', 'gender' => 'L', 'birth_date' => '1996-09-19', 'education' => 'D3', 'major' => 'Pemasaran',              'gpa' => 3.10, 'age' => 30, 'address' => 'Jl. Lily No. 8, Surabaya'],
            ['name' => 'Hana Safitri',         'email' => 'hana.s@mail.com',    'phone' => '081456789013', 'gender' => 'P', 'birth_date' => '1999-08-07', 'education' => 'S1', 'major' => 'Bahasa Inggris',         'gpa' => 3.72, 'age' => 27, 'address' => 'Jl. Orchid No. 21, Surabaya'],
        ];

        $createdApplicants3 = [];
        foreach ($applicants3 as $a) {
            $createdApplicants3[] = Applicant::create([
                'period_id' => $period3->id,
                ...$a,
            ]);
        }

        $scores3 = [
            [5, 4, 4, 4, 5, 3, 4], // Putri
            [5, 4, 4, 3, 4, 3, 3], // Rizky
            [3, 3, 3, 3, 3, 2, 3], // Dina
            [5, 5, 5, 4, 4, 5, 4], // Ahmad
            [5, 4, 4, 4, 5, 3, 4], // Lina
            [5, 3, 3, 3, 3, 2, 2], // Yoga
            [3, 4, 3, 2, 3, 3, 3], // Mega
            [5, 4, 5, 5, 5, 4, 5], // Ilham
            [5, 3, 4, 5, 4, 3, 4], // Ratna
            [5, 4, 4, 3, 3, 4, 3], // Bayu
            [5, 3, 4, 3, 4, 3, 4], // Citra
            [3, 5, 3, 2, 3, 3, 2], // Galih
            [5, 4, 4, 5, 5, 3, 4], // Hana
        ];

        foreach ($createdApplicants3 as $idx => $applicant) {
            foreach ($criteriaModels as $cIdx => $criteriaModel) {
                Evaluation::create([
                    'period_id' => $period3->id,
                    'applicant_id' => $applicant->id,
                    'criteria_id' => $criteriaModel->id,
                    'score' => $scores3[$idx][$cIdx],
                ]);
            }
        }
    }
}
