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

        $direkturUser = User::create([
            'name' => 'Direktur',
            'email' => 'direktur@spk.com',
            'password' => bcrypt('password'),
            'role' => 'direktur',
        ]);

        $hrdUser = User::create([
            'name' => 'Kepala HRD',
            'email' => 'hrd@spk.com',
            'password' => bcrypt('password'),
            'role' => 'evaluator',
        ]);

        $managerUser = User::create([
            'name' => 'Manager Unit',
            'email' => 'manager@spk.com',
            'password' => bcrypt('password'),
            'role' => 'evaluator',
        ]);

        Evaluator::where('code', 'default')->whereDoesntHave('evaluations')->delete();

        Evaluator::create([
            'code' => 'HRD',
            'name' => 'Divisi HRD',
            'role_label' => 'HRD',
            'user_id' => $hrdUser->id,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Evaluator::create([
            'code' => 'MGR',
            'name' => 'Manager',
            'role_label' => 'Manager',
            'user_id' => $managerUser->id,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Evaluator::create([
            'code' => 'DIR',
            'name' => 'Direktur',
            'role_label' => 'Direktur',
            'user_id' => $direkturUser->id,
            'sort_order' => 3,
            'is_active' => true,
        ]);


        foreach ($this->criteriaDefinitionsAdministrasiTu() as $def) {
            $this->createCriteriaWithSubs($def);
        }

        foreach ($this->criteriaDefinitionsTeknisIt() as $def) {
            $this->createCriteriaWithSubs($def);
        }

        /** @var Collection<int,Evaluator> $evaluatorsOrd */
        $evaluatorsOrd = Evaluator::orderBy('sort_order')->get();

        /*
         * Periode 1 — Staff TU & pendamping akademik (8 pelamar).
         * Bobot relatif: komunikasi layanan (TU2), ketelitian dokumen (TU3), dan wawancara (TU7) cenderung lebih besar sesuai prioritas posisi TU.
         */
        $period = SelectionPeriod::create([
            'name' => 'Rekrutmen Staff TU & Pendamping Akademik — Sem. Gasal 2026',
            'position' => 'Staff Tata Usaha / Pendamping Akademik',
            'start_date' => '2026-02-10',
            'end_date' => '2026-04-30',
            'description' => 'Penerimaan tenaga administrasi sekolah, layanan mahasiswa, dan pendamping program akademik. Penekanan pada komunikasi, kerapian dokumen, dan etos layanan.',
            'status' => 'open',
            'created_by' => 1,
        ]);

        $this->seedPeriodLinkedCriteriaRelative($period, [
            'TU1' => 16,
            'TU2' => 20,
            'TU3' => 18,
            'TU4' => 14,
            'TU5' => 12,
            'TU6' => 13,
            'TU7' => 17,
        ]);

        $applicants = [
            ['name' => 'Candradewi Kartika', 'email' => 'c.dewi.kartika@mail.id', 'phone' => '081112223301', 'gender' => 'P', 'birth_date' => '1996-04-12', 'education' => 'S2', 'major' => 'Manajemen Pendidikan', 'gpa' => 3.81, 'age' => 30, 'address' => 'Jl. Ciumbuleuit No. 88, Bandung'],
            ['name' => 'Eko Prasetyo Wibowo', 'email' => 'eko.prasetyo@mail.id', 'phone' => '081112223302', 'gender' => 'L', 'birth_date' => '1998-09-03', 'education' => 'S1', 'major' => 'Administrasi Publik', 'gpa' => 3.22, 'age' => 28, 'address' => 'Perum Cimahi Permai Blok A/7, Cimahi'],
            ['name' => 'Fitriani Lestari', 'email' => 'fitriani.lestari@mail.id', 'phone' => '081112223303', 'gender' => 'P', 'birth_date' => '2001-11-20', 'education' => 'D3', 'major' => 'Administrasi Perkantoran', 'gpa' => 3.48, 'age' => 25, 'address' => 'Jl. Mayor Abdurachman No. 15, Sumedang'],
            ['name' => 'Gilang Ramadhan', 'email' => 'gilang.ramadhan@mail.id', 'phone' => '081112223304', 'gender' => 'L', 'birth_date' => '1999-02-18', 'education' => 'S1', 'major' => 'Ilmu Komunikasi', 'gpa' => 3.58, 'age' => 27, 'address' => 'Jl. Soekarno-Hatta No. 402, Bandung'],
            ['name' => 'Hesti Munawaroh', 'email' => 'hesti.munawaroh@mail.id', 'phone' => '081112223305', 'gender' => 'P', 'birth_date' => '2000-07-05', 'education' => 'S1', 'major' => 'Pendidikan Bahasa Indonesia', 'gpa' => 3.67, 'age' => 26, 'address' => 'Jl. Perintis Kemerdekaan No. 3, Tasikmalaya'],
            ['name' => 'Irfan Hakim', 'email' => 'irfan.hakim@mail.id', 'phone' => '081112223306', 'gender' => 'L', 'birth_date' => '1994-12-01', 'education' => 'S2', 'major' => 'Manajemen', 'gpa' => 3.74, 'age' => 32, 'address' => 'Kompleks Setra Dago Asri No. 12, Bandung'],
            ['name' => 'Jihan Safitri', 'email' => 'jihan.safitri@mail.id', 'phone' => '081112223307', 'gender' => 'P', 'birth_date' => '1997-03-28', 'education' => 'S1', 'major' => 'Hukum', 'gpa' => 3.18, 'age' => 29, 'address' => 'Jl. Otista No. 55, Garut'],
            ['name' => 'Kurniawan Adi Nugroho', 'email' => 'kurniawan.adi@mail.id', 'phone' => '081112223308', 'gender' => 'L', 'birth_date' => '2002-05-14', 'education' => 'D3', 'major' => 'Manajemen Informatika', 'gpa' => 3.31, 'age' => 24, 'address' => 'Jl. Pelajar Pejuang No. 210, Bandung'],
        ];

        $createdApplicants = [];
        foreach ($applicants as $a) {
            $createdApplicants[] = Applicant::create([
                'period_id' => $period->id,
                ...$a,
            ]);
        }

        // Urutan kolom = TU1..TU7: pendidikan relevan TU, komunikasi layanan, kerapian dokumen, perkantoran digital, aturan akademik, pengalaman adm/pendidikan, wawancara & budaya kerja
        $scores = [
            [5, 4, 5, 5, 5, 4, 5],
            [4, 4, 4, 3, 4, 3, 3],
            [3, 4, 4, 4, 3, 2, 4],
            [4, 5, 3, 4, 4, 2, 5],
            [4, 5, 4, 4, 4, 2, 4],
            [5, 4, 5, 4, 4, 5, 5],
            [4, 3, 3, 2, 2, 3, 3],
            [3, 4, 3, 4, 3, 2, 4],
        ];

        $this->seedMultiKmkkEvaluations($period->id, $createdApplicants, $scores, $evaluatorsOrd);

        /*
         * Periode 2 — Teknisi lab & IT support (7 pelamar).
         * Bobot relatif: hard skill jaringan/OS (IT2), rekam jejak akademik (IT3), simulasi troubleshoot (IT5), dan pengalaman dukungan (IT6) mendominasi.
         */
        $period2 = SelectionPeriod::create([
            'name' => 'Rekrutmen Teknisi Lab Komputer & IT Support — 2026',
            'position' => 'Teknisi Laboratorium / IT Support',
            'start_date' => '2026-07-01',
            'end_date' => '2026-09-15',
            'description' => 'Rekrutmen untuk perawatan perangkat lab, jaringan kampus ringan, dan dukungan teknis harian. Diutamakan basis teknis kuat dan kemampuan troubleshooting.',
            'status' => 'closed',
            'created_by' => 1,
        ]);

        $this->seedPeriodLinkedCriteriaRelative($period2, [
            'IT1' => 12,
            'IT2' => 22,
            'IT3' => 21,
            'IT4' => 9,
            'IT5' => 14,
            'IT6' => 17,
            'IT7' => 15,
        ]);

        $applicants2 = [
            ['name' => 'Lutfi Andrean', 'email' => 'lutfi.andrean@mail.id', 'phone' => '081223334401', 'gender' => 'L', 'birth_date' => '1999-08-22', 'education' => 'S1', 'major' => 'Teknik Informatika', 'gpa' => 3.72, 'age' => 27, 'address' => 'Jl. Kyai Mojo No. 14, Yogyakarta'],
            ['name' => 'Melati Sari Dewi', 'email' => 'melati.sari@mail.id', 'phone' => '081223334402', 'gender' => 'P', 'birth_date' => '2000-01-30', 'education' => 'S1', 'major' => 'Sistem Informasi', 'gpa' => 3.88, 'age' => 26, 'address' => 'Jl. Pandega Marta No. 8A, Sleman'],
            ['name' => 'Nanda Pratama', 'email' => 'nanda.pratama@mail.id', 'phone' => '081223334403', 'gender' => 'L', 'birth_date' => '2003-04-09', 'education' => 'D3', 'major' => 'Manajemen Informatika', 'gpa' => 3.19, 'age' => 23, 'address' => 'Jl. Magelang Km 5, Yogyakarta'],
            ['name' => 'Oka Setiawan', 'email' => 'oka.setiawan@mail.id', 'phone' => '081223334404', 'gender' => 'L', 'birth_date' => '1998-11-17', 'education' => 'S1', 'major' => 'Teknik Informatika', 'gpa' => 3.41, 'age' => 28, 'address' => 'Perum Graha Sewu Indah Blok D/5, Yogyakarta'],
            ['name' => 'Putri Anggraini', 'email' => 'putri.anggraini@mail.id', 'phone' => '081223334405', 'gender' => 'P', 'birth_date' => '1996-06-02', 'education' => 'S2', 'major' => 'Ilmu Komputer', 'gpa' => 3.91, 'age' => 30, 'address' => 'Jl. Solo Km 12, Kalasan, Sleman'],
            ['name' => 'Qori Sandria Erlangga', 'email' => 'qori.sandria@mail.id', 'phone' => '081223334406', 'gender' => 'L', 'birth_date' => '2001-10-25', 'education' => 'S1', 'major' => 'Teknik Elektro', 'gpa' => 3.36, 'age' => 25, 'address' => 'Jl. Babarsari No. 44, Condongcatur'],
            ['name' => 'Restu Wijaya Kusuma', 'email' => 'restu.wijaya@mail.id', 'phone' => '081223334407', 'gender' => 'L', 'birth_date' => '2002-12-08', 'education' => 'S1', 'major' => 'Teknik Informatika', 'gpa' => 2.95, 'age' => 24, 'address' => 'Jl. Kaliurang Km 8,5, Sleman'],
        ];

        $createdApplicants2 = [];
        foreach ($applicants2 as $a) {
            $createdApplicants2[] = Applicant::create([
                'period_id' => $period2->id,
                ...$a,
            ]);
        }

        // Kolom IT1..IT7: relevansi pendidikan TI, jaringan & OS, rekaman akademik (IPK), komunikasi pengguna akhir, wawancara troubleshooting, pengalaman dukungan TI/lab, penalaran logika & tes teknis
        $scores2 = [
            [4, 5, 5, 4, 4, 4, 4],
            [4, 5, 5, 5, 4, 4, 5],
            [3, 3, 3, 3, 3, 3, 3],
            [4, 4, 3, 4, 4, 3, 4],
            [5, 5, 5, 4, 5, 5, 5],
            [3, 3, 4, 4, 3, 2, 3],
            [3, 2, 2, 2, 2, 2, 2],
        ];

        $this->seedMultiKmkkEvaluations($period2->id, $createdApplicants2, $scores2, $evaluatorsOrd);

        $aggregator = app(GroupDecisionAggregator::class);
        foreach ([$period, $period2] as $p) {
            $aggregator->rebuild((int) $p->id, 'average');
        }

        $this->command?->info('Seeder selesai: kriteria TU & IT dengan sub-skala tersendiri, 2 periode (8+7 pelamar), agregasi KMKK.');
    }

    /**
     * @param  array<string, float|int>  $relativeWeightsByCriterionCode  Urutan kunci = urutan tampilan pivot; nilai relatif positif (dinormalisasi ke jumlah 1).
     */
    private function seedPeriodLinkedCriteriaRelative(SelectionPeriod $period, array $relativeWeightsByCriterionCode): void
    {
        $sum = array_sum($relativeWeightsByCriterionCode);
        if ($sum <= 0) {
            throw new \InvalidArgumentException('Total bobot relatif kriteria harus > 0.');
        }

        $syncData = [];
        $order = 0;
        foreach ($relativeWeightsByCriterionCode as $code => $rel) {
            $criteria = Criteria::where('code', $code)->firstOrFail();
            $syncData[$criteria->id] = ['sort_order' => $order++];
        }

        $period->linkedCriteria()->sync($syncData);

        CriteriaWeight::where('period_id', $period->id)->delete();

        foreach ($relativeWeightsByCriterionCode as $code => $rel) {
            $criteria = Criteria::where('code', $code)->firstOrFail();
            CriteriaWeight::create([
                'period_id' => $period->id,
                'criteria_id' => $criteria->id,
                'weight' => round(((float) $rel) / $sum, 6),
            ]);
        }
    }

    /**
     * @param  array<int, Applicant>  $applicantsOrdered
     */
    private function seedMultiKmkkEvaluations(int $periodId, array $applicantsOrdered, array $scoreMatrix, Collection $evaluatorsOrd): void
    {
        $criteriaModels = SelectionPeriod::find($periodId)?->linkedCriteria()->orderByPivot('sort_order')->get()
            ?? Criteria::orderBy('code')->get();
        $deltasPerEvaluatorIdx = [-1, 0, 1];

        foreach (array_values($applicantsOrdered) as $idx => $applicant) {
            foreach ($criteriaModels as $cIdx => $criteriaModel) {
                $base = $scoreMatrix[$idx][$cIdx];
                foreach ($evaluatorsOrd->values() as $ei => $evaluatorRow) {
                    $delta = $deltasPerEvaluatorIdx[$ei] ?? 0;
                    $scoreVal = max(1, min(5, $base + $delta));
                    Evaluation::create([
                        'period_id' => $periodId,
                        'applicant_id' => $applicant->id,
                        'criteria_id' => $criteriaModel->id,
                        'evaluator_id' => $evaluatorRow->id,
                        'score' => $scoreVal,
                    ]);
                }
            }
        }
    }

    /** @phpstan-return list<array{code: string, name: string, type: string, importance: int, description: ?string, sub: list<array{name: string, value: positive-int}>}> */
    private function criteriaDefinitionsAdministrasiTu(): array
    {
        return [
            [
                'code' => 'TU1',
                'name' => 'Pendidikan formal & kesesuaian bidang dengan tugas TU/akademik',
                'description' => 'Kelulusan terakhir dan relevansi disiplin ilmu untuk administrasi perguruan, layanan akademik, atau norma sekolahan.',
                'type' => 'benefit',
                'importance' => 10,
                'sub' => [
                    ['name' => 'Tidak ada / tidak sesuai dengan administrasi atau pendidikan', 'value' => 1],
                    ['name' => 'SMK/SMA (kurang linear dengan TU akademik tinggi)', 'value' => 2],
                    ['name' => 'D3 administrasi sekretariat/akuntansi/perkantoran', 'value' => 3],
                    ['name' => 'S1 administrasi pub., komunikasi, pendidikan, hukum perguruan', 'value' => 4],
                    ['name' => 'S2/S3 atau S1 jurusan sangat selaras dengan administrasi akademik', 'value' => 5],
                ],
            ],
            [
                'code' => 'TU2',
                'name' => 'Komunikasi interpersonal & kemampuan melayani stakeholder',
                'description' => 'Kepraktisan komunikasi sopan kepada mahasiswa, orang tua/wali, atau dosen lewat tulisan atau lisan.',
                'type' => 'benefit',
                'importance' => 9,
                'sub' => [
                    ['name' => 'Sangat kaku/asertif tinggi atau sulit menyampaikan secara sopan', 'value' => 1],
                    ['name' => 'Kurang jelas atau sering salah paham', 'value' => 2],
                    ['name' => 'Standar sopan santun kantor perguruan', 'value' => 3],
                    ['name' => 'Menenangkan situasi konflik kecil secara profesional', 'value' => 4],
                    ['name' => 'Sangat empatik, jelas, dan konsisten melayani', 'value' => 5],
                ],
            ],
            [
                'code' => 'TU3',
                'name' => 'Ketepatan dokumen akademik & tata penyimpanan berkas fisik/digital',
                'description' => 'Kemampuan memeriksa KRS, ijazah, transkrip, dan arsip akademik secara rapi serta minim kesalahan entri.',
                'type' => 'benefit',
                'importance' => 8,
                'sub' => [
                    ['name' => 'Banyak salah ketik/format sesuai aturan akademik', 'value' => 1],
                    ['name' => 'Sesekali salah label berkas atau data entri', 'value' => 2],
                    ['name' => 'Konsisten rapi tetapi perlu banyak pengawasan detail', 'value' => 3],
                    ['name' => 'Hampir bebas salah dan mengikuti SOP dokumentasi mandiri', 'value' => 4],
                    ['name' => 'Sangat teliti dapat melakukan quality check untuk tim lain', 'value' => 5],
                ],
            ],
            [
                'code' => 'TU4',
                'name' => 'Penguasaan aplikasi perkantoran (spreadsheet, formulir daring, LMS ringan)',
                'description' => 'Penguasaan paket office atau sistem administrasi perguruan sederhana (misal Google Form, LMS entri nilai dasar).',
                'type' => 'benefit',
                'importance' => 7,
                'sub' => [
                    ['name' => 'Hanya menguasai mengetik dokumen sangat minimal', 'value' => 1],
                    ['name' => 'Bisa spreadsheet tetapi formula dasar tidak lancar', 'value' => 2],
                    ['name' => 'Menguasai tabel spreadsheet & mail merge formulir mahasiswa', 'value' => 3],
                    ['name' => 'Bisa bikin rekapitulasi & dashboard laporan sederhana', 'value' => 4],
                    ['name' => 'Mahir otomasi formulir/skrip ringan serta adaptasi LMS cepat', 'value' => 5],
                ],
            ],
            [
                'code' => 'TU5',
                'name' => 'Pemahaman peraturan akademik internal & etika penyimpanan data',
                'description' => 'Memahami alur akademik perguruan, kerahasiaan data mahasiswa, dan kepatuhan dokumentasi regulatorik ringan.',
                'type' => 'benefit',
                'importance' => 6,
                'sub' => [
                    ['name' => 'Tidak memahami SOP akademik perguruan', 'value' => 1],
                    ['name' => 'Tahu sebagian tetapi perlu penyuluhan rutin tiap tugas baru', 'value' => 2],
                    ['name' => 'Bisa jalankan tugas sesuai SOP dokumentasi akademik ketat reguler', 'value' => 3],
                    ['name' => 'Menguasai SOP serta dapat sosialisasi ke unit lain', 'value' => 4],
                    ['name' => 'Andal sebagai second opinion kepatuhan data mahasiswa', 'value' => 5],
                ],
            ],
            [
                'code' => 'TU6',
                'name' => 'Pengalaman kerja di administrasi perguruan, sekolahan, atau lembaga pelayanan akademik serupa',
                'description' => 'Riwayat praktik di TU fakultas, kampus, akademi, pusat bahasa, dll.',
                'type' => 'benefit',
                'importance' => 5,
                'sub' => [
                    ['name' => 'Belum pernah mendukung proses akademik secara formal full-time', 'value' => 1],
                    ['name' => 'Magang part-time sekurang-kurangnya 3 bulan TU serupa', 'value' => 2],
                    ['name' => '1–12 bulan TU/sekretariat perguruan penuh-time', 'value' => 3],
                    ['name' => '1–3 tahun lapangan dokumentasi akademik berkesinambungan', 'value' => 4],
                    ['name' => 'Lebih dari 3 tahun senioritas berkas akademik perguruan', 'value' => 5],
                ],
            ],
            [
                'code' => 'TU7',
                'name' => 'Wawancara perilaku profesionalisme, integritas layanan & budaya kerja',
                'description' => 'Konsistensi jawaban tentang etos kerja, penanganan komplain, dan fleksibilitas jam padat layanan.',
                'type' => 'benefit',
                'importance' => 8,
                'sub' => [
                    ['name' => 'Jawaban defensif/tidak menunjukkan komitmen layanan', 'value' => 1],
                    ['name' => 'Umum saja sehingga sulit menilai integritas', 'value' => 2],
                    ['name' => 'Menunjukkan pemahaman budaya layanan perguruan standar', 'value' => 3],
                    ['name' => 'Contoh nyata menangani kasus nyata pelanggan internal', 'value' => 4],
                    ['name' => 'Sangat matang, reflektif, dan adaptif terhadap kebutuhan institusi', 'value' => 5],
                ],
            ],
        ];
    }

    /** @phpstan-return list<array{code: string, name: string, type: string, importance: int, description: ?string, sub: list<array{name: string, value: positive-int}>}> */
    private function criteriaDefinitionsTeknisIt(): array
    {
        return [
            [
                'code' => 'IT1',
                'name' => 'Relevansi gelar & dasar pendidikan teknologi informasi',
                'description' => 'Sejauh mana riwayat studi mendukung troubleshooting perangkat keras/lunak di laboratorium.',
                'type' => 'benefit',
                'importance' => 9,
                'sub' => [
                    ['name' => 'Jurusan tidak berkaitan dengan TI / belum pernah kursus teknis', 'value' => 1],
                    ['name' => 'Jurusan campuran (elektro/hukum) dengan kursus TI dasar', 'value' => 2],
                    ['name' => 'D3/S1 MI/SI dengan praktikum jaringan atau pemrograman ringan', 'value' => 3],
                    ['name' => 'S1 TI/RPL dengan praktik lab hardware & software terstruktur', 'value' => 4],
                    ['name' => 'S2 Ilmu Komputer/TI atau sertifikasi industri relevan (mis. CCNA entry)', 'value' => 5],
                ],
            ],
            [
                'code' => 'IT2',
                'name' => 'Pengetahuan praktis jaringan kabel/nirkabel & sistem operasi workstation/server ringan',
                'description' => 'Kemampuan konfigurasi DHCP statis, VLAN sederhana, imaging OS lab, dan perawatan patch security dasar.',
                'type' => 'benefit',
                'importance' => 10,
                'sub' => [
                    ['name' => 'Hanya tahu pengguna akhir umum (browsing & office)', 'value' => 1],
                    ['name' => 'Bisa instal driver dasar tetapi tidak pernah urus topologi jaringan', 'value' => 2],
                    ['name' => 'Bisa troubleshooting Wi-Fi lokal & recovery windows standar', 'value' => 3],
                    ['name' => 'Mampu kelola switch managed entry-level & server file share', 'value' => 4],
                    ['name' => 'Menguasai imaging massal lab, VLAN guest, dan monitoring resource', 'value' => 5],
                ],
            ],
            [
                'code' => 'IT3',
                'name' => 'Rekam jejak akademik kognitif (IPK) untuk substansi teknis',
                'description' => 'Indikator komitmen belajar materi abstrak/matematika logika yang lazim diasosiasikan dengan kinerja teknisi junior.',
                'type' => 'benefit',
                'importance' => 8,
                'sub' => [
                    ['name' => 'IPK < 2.50 atau historis banyak mata kuliah ulang kritikal TI', 'value' => 1],
                    ['name' => 'IPK 2.50 – 2.89', 'value' => 2],
                    ['name' => 'IPK 3.00 – 3.34', 'value' => 3],
                    ['name' => 'IPK 3.35 – 3.74 dengan bukti aktivitas ekstrakurikuler teknis', 'value' => 4],
                    ['name' => 'IPK ≥ 3.75 atau penyerta proyek teknologi publikasi', 'value' => 5],
                ],
            ],
            [
                'code' => 'IT4',
                'name' => 'Komunikasi teknis ke pengguna awam & dokumentasi tiket dukungan ringkas',
                'description' => 'Menjelaskan solusi secara non jargon dan mencatat log intervensi konsisten dalam sistem ticketing kampus.',
                'type' => 'benefit',
                'importance' => 6,
                'sub' => [
                    ['name' => 'Suka menyalahkan pengguna & tidak dokumentasikan apa pun', 'value' => 1],
                    ['name' => 'Catatan dukungan tersebar atau sulit dibaca rekannya', 'value' => 2],
                    ['name' => 'Bisa dokumentasi standar SOP ticketing institusional', 'value' => 3],
                    ['name' => 'Menggunakan bahasa sederhana sehingga dosen cepat mengerti workaround', 'value' => 4],
                    ['name' => 'Panduan self-help singkat bagi pengguna bisa diproduksi mandiri', 'value' => 5],
                ],
            ],
            [
                'code' => 'IT5',
                'name' => 'Wawancara simulasi kasus troubleshooting mendesak di laboratorium',
                'description' => 'Pemikiran struktur atas kasus gagal booting massal atau jaring lab mati mendadak secara metodis.',
                'type' => 'benefit',
                'importance' => 9,
                'sub' => [
                    ['name' => 'Tanpa struktur penyelidikan (random coba)', 'value' => 1],
                    ['name' => 'Menyebut gejala tetapi gagal menyusun prioritas penyebab masalah', 'value' => 2],
                    ['name' => 'Mengikuti checklist standar penyelidikan lab', 'value' => 3],
                    ['name' => 'Beradaptasi secara iteratif serta mengkomunikasikan trade-off cepat versus aman', 'value' => 4],
                    ['name' => 'Menunjukkan pattern recognition masalah rekuren kampus Anda', 'value' => 5],
                ],
            ],
            [
                'code' => 'IT6',
                'name' => 'Pengalaman support IT / maintenance lab komputer atau helpdesk kampus industri',
                'description' => 'Durasi serta kedalaman menghadapi SLA perbaikan workstation laboratorium perguruan.',
                'type' => 'benefit',
                'importance' => 8,
                'sub' => [
                    ['name' => 'Tanpa pengalaman formil support/perawatan workstation publik banyak', 'value' => 1],
                    ['name' => 'Pernah membantu satu project maintenance singkat tidak berkelanjutan', 'value' => 2],
                    ['name' => 'Minimal 6 bulan helpdesk perguruan full-time paralel studi atau setelah kelulusan', 'value' => 3],
                    ['name' => '1–2 tahun dukungan workstation lab akademik banyak unit', 'value' => 4],
                    ['name' => 'Lebih dari 2 tahun memimpin aktivasi lab semesteran massal perguruan', 'value' => 5],
                ],
            ],
            [
                'code' => 'IT7',
                'name' => 'Penalaran logika & tes teknis cepat pola riset operasional sederhana',
                'description' => 'Misalnya tes numerik pola, klasifikasi penyebab kegagalan, atau mini coding pseudocode troubleshoot.',
                'type' => 'benefit',
                'importance' => 7,
                'sub' => [
                    ['name' => 'Skor tes logika/teknis di bawah standar kelulusan organisasi Anda', 'value' => 1],
                    ['name' => 'Hanya memenuhi ambang kelulusan tetapi lambat secara waktu', 'value' => 2],
                    ['name' => 'Memenuhi target waktu serta akurasi rata median', 'value' => 3],
                    ['name' => 'Akurasi di atas median dengan komunikasi cara berpikir jelas tertulis singkat', 'value' => 4],
                    ['name' => 'Outstanding baik cepat serta menemukan pola tidak biasa tes', 'value' => 5],
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
                'name' => $sub['name'],
                'value' => $sub['value'],
            ]);
        }
    }
}
