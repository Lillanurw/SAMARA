<?php

namespace Database\Seeders;

use App\Enums\ActivityType;
use App\Enums\DirectorInputStatus;
use App\Enums\DirectorInputType;
use App\Enums\FollowUpStatus;
use App\Enums\OutcomeType;
use App\Enums\PlanStatus;
use App\Enums\PriorityLevel;
use App\Enums\PriorityTier;
use App\Enums\SubmitStatus;
use App\Enums\UserRole;
use App\Models\AppSetting;
use App\Models\Area;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\DirectorInput;
use App\Models\FollowUp;
use App\Models\Segment;
use App\Models\User;
use App\Models\VisitPlan;
use App\Models\VisitReport;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Cleanup: remove all transactional data to allow re-seeding ──
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \App\Models\VisitReport::truncate();
        DB::table('visit_plan_members')->truncate();
        \App\Models\VisitPlan::truncate();
        \App\Models\FollowUp::truncate();
        \App\Models\DirectorInput::truncate();
        \App\Models\Customer::truncate();
        DB::table('user_areas')->truncate();
        \App\Models\Segment::truncate();
        \App\Models\Area::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Users
        $admin = User::firstOrCreate(
            ['email' => 'Admin@pastac.co.id'],
            [
                'password' => Hash::make('password123'),
                'full_name' => 'Admin - Lilla Nur (LN)',
                'role' => UserRole::ADMIN->value,
                'is_active' => true,
            ]
        );

        $director = User::firstOrCreate(
            ['email' => 'Ghaffari@pastac.co.id'],
            [
                'password' => Hash::make('password123'),
                'full_name' => 'Direktur SMD - Muhammad Ghaffari (MG)',
                'role' => UserRole::DIRECTOR->value,
                'is_active' => true,
            ]
        );

        $directorProyek = User::firstOrCreate(
            ['email' => 'Wandono@pastac.co.id'],
            [
                'password' => Hash::make('password123'),
                'full_name' => 'Direktur Proyek - Wandono Rino (WR)',
                'role' => UserRole::DIRECTOR->value,
                'is_active' => true,
            ]
        );

        $tim1 = User::firstOrCreate(
            ['email' => 'Gunardo@pastac.co.id'],
            [
                'password' => Hash::make('password123'),
                'full_name' => 'Sales Executive - Gunardo Probojakti (GP)',
                'role' => UserRole::TIM->value,
                'manager_email' => 'Ghaffari@pastac.co.id',
                'is_active' => true,
            ]
        );

        $tim2 = User::firstOrCreate(
            ['email' => 'Fikri@pastac.co.id'],
            [
                'password' => Hash::make('password123'),
                'full_name' => 'Sales Executive - Fikri Ardisa (FA)',
                'role' => UserRole::TIM->value,
                'manager_email' => 'Ghaffari@pastac.co.id',
                'is_active' => true,
            ]
        );

        $tim3 = User::firstOrCreate(
            ['email' => 'Wera@pastac.co.id'],
            [
                'password' => Hash::make('password123'),
                'full_name' => 'Sales Executive - Wera Sauma (WS)',
                'role' => UserRole::TIM->value,
                'manager_email' => 'Ghaffari@pastac.co.id',
                'is_active' => true,
            ]
        );

        // 2. Areas
        $areaWest = Area::firstOrCreate(
            ['area_code' => 'AREA-WEST'],
            [
                'area_name' => 'Wilayah Barat (DKI & Jawa Barat)',
                'region' => 'Wilayah Barat',
                'description' => 'Cakupan operasional DKI Jakarta, Bogor, Depok, Tangerang, Bekasi, dan Bandung.',
                'is_active' => true,
            ]
        );

        $areaCentral = Area::firstOrCreate(
            ['area_code' => 'AREA-CENTRAL'],
            [
                'area_name' => 'Wilayah Tengah (Jawa Tengah & DIY)',
                'region' => 'Wilayah Tengah',
                'description' => 'Cakupan operasional Semarang, Surakarta, Magelang, dan D.I. Yogyakarta.',
                'is_active' => true,
            ]
        );

        $areaEast = Area::firstOrCreate(
            ['area_code' => 'AREA-EAST'],
            [
                'area_name' => 'Wilayah Timur (Jawa Timur & Bali)',
                'region' => 'Wilayah Timur',
                'description' => 'Cakupan operasional Surabaya, Malang, Madiun, dan Denpasar.',
                'is_active' => true,
            ]
        );

        // Assign Areas
        $admin->areas()->sync([$areaWest->id, $areaCentral->id, $areaEast->id]);
        $director->areas()->sync([$areaWest->id, $areaCentral->id, $areaEast->id]);
        $directorProyek->areas()->sync([$areaWest->id, $areaCentral->id, $areaEast->id]);
        $tim1->areas()->sync([$areaWest->id, $areaCentral->id]);
        $tim2->areas()->sync([$areaWest->id, $areaEast->id]);
        $tim3->areas()->sync([$areaCentral->id, $areaEast->id]);

        // 3. Segments
        $seg1 = Segment::firstOrCreate(
            ['segment_code' => 'SEG-PERMIL'],
            [
                'segment_name' => 'Perlengkapan Perorangan & Lapangan',
                'description' => 'Seragam tactical, sepatu boots, rompi perlindungan, helm lapangan, dan tas dinas.',
                'is_active' => true,
            ]
        );

        $seg2 = Segment::firstOrCreate(
            ['segment_code' => 'SEG-KOMUNIKASI'],
            [
                'segment_name' => 'Perangkat Komunikasi Tactical',
                'description' => 'Radio HT hand-held, repeater lapangan, headset peredam bising, dan power pack.',
                'is_active' => true,
            ]
        );

        $seg3 = Segment::firstOrCreate(
            ['segment_code' => 'SEG-KENDARAAN'],
            [
                'segment_name' => 'Komponen & Aksesori Kendaraan Khusus',
                'description' => 'Suku cadang khusus, winch heavy duty, lampu sorot led tactical, dan jok kenyamanan khusus.',
                'is_active' => true,
            ]
        );

        // 4. Customers (Based on Sales & Marketing Tools Presentation - PT Persada Aman Sentosa / PASTAC)
        $custsData = [
            // Gunardo Probojakti (GP) - Group AL / Matlog / Pussenkav / Kopasgat
            [
                'customer_code' => 'CUST-001',
                'customer_name' => 'Mabesal (Mabes TNI AL)',
                'area_id' => $areaWest->id,
                'segment_id' => $seg2->id,
                'owner_id' => $tim1->id,
                'address' => 'Jl. Raya Hankam, Cilangkap',
                'city' => 'Jakarta Timur',
                'province' => 'DKI Jakarta',
                'contact_name' => 'Laksma TNI Supriadi',
                'contact_position' => 'Aslog KSAL',
                'contact_phone' => '081299112233',
                'contact_email' => 'aslog@mabesal.mil.id',
                'priority_tier' => PriorityTier::A->value,
                'notes' => 'Fokus komunikasi tactical & perlengkapan matlog TNI AL.',
            ],
            [
                'customer_code' => 'CUST-002',
                'customer_name' => 'Kormar (Korps Marinir RI)',
                'area_id' => $areaWest->id,
                'segment_id' => $seg1->id,
                'owner_id' => $tim1->id,
                'address' => 'Jl. Prapatan No. 40',
                'city' => 'Jakarta Pusat',
                'province' => 'DKI Jakarta',
                'contact_name' => 'Kolonel Mar Heru',
                'contact_position' => 'Aslog Dankormar',
                'contact_phone' => '081388223344',
                'contact_email' => 'aslog@kormar.mil.id',
                'priority_tier' => PriorityTier::A->value,
                'notes' => 'Pengadaan perlengkapan kaporlap & seragam perorangan marinir.',
            ],
            [
                'customer_code' => 'CUST-003',
                'customer_name' => 'Denjaka (Detasemen Jala Mangkara)',
                'area_id' => $areaWest->id,
                'segment_id' => $seg1->id,
                'owner_id' => $tim1->id,
                'address' => 'Ksatrian Arthur Solang, Cilandak',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'contact_name' => 'Mayor Mar Triyono',
                'contact_position' => 'Pasilog Denjaka',
                'contact_phone' => '081177334455',
                'contact_email' => 'pasilog@denjaka.mil.id',
                'priority_tier' => PriorityTier::A->value,
                'notes' => 'Peralatan tactical khusus penanggulangan teror kelautan.',
            ],
            [
                'customer_code' => 'CUST-004',
                'customer_name' => 'Koarmada RI',
                'area_id' => $areaWest->id,
                'segment_id' => $seg3->id,
                'owner_id' => $tim1->id,
                'address' => 'Jl. Gunung Sahari No. 67',
                'city' => 'Jakarta Pusat',
                'province' => 'DKI Jakarta',
                'contact_name' => 'Kolonel Laut Bambang',
                'contact_position' => 'Aslog Koarmada RI',
                'contact_phone' => '081566445566',
                'contact_email' => 'aslog@koarmada.mil.id',
                'priority_tier' => PriorityTier::A->value,
                'notes' => 'Kebutuhan perlengkapan kapal operasi patroli laut.',
            ],
            [
                'customer_code' => 'CUST-005',
                'customer_name' => 'Kopaska (Komando Pasukan Katak)',
                'area_id' => $areaWest->id,
                'segment_id' => $seg1->id,
                'owner_id' => $tim1->id,
                'address' => 'Kawasan Pesisir Kemayoran',
                'city' => 'Jakarta Utara',
                'province' => 'DKI Jakarta',
                'contact_name' => 'Letkol Laut Danang',
                'contact_position' => 'Danpuskopaska',
                'contact_phone' => '081755556677',
                'contact_email' => 'danang@kopaska.mil.id',
                'priority_tier' => PriorityTier::B->value,
                'notes' => 'Perlengkapan selam tactical & alat komunikasi bawah air.',
            ],
            [
                'customer_code' => 'CUST-006',
                'customer_name' => 'Intaifib (Batalyon Intai Amfibi)',
                'area_id' => $areaWest->id,
                'segment_id' => $seg1->id,
                'owner_id' => $tim1->id,
                'address' => 'Marunda, Cilincing',
                'city' => 'Jakarta Utara',
                'province' => 'DKI Jakarta',
                'contact_name' => 'Mayor Mar Agus',
                'contact_position' => 'Dantaifib',
                'contact_phone' => '081244667788',
                'contact_email' => 'dantaifib@intaifib.mil.id',
                'priority_tier' => PriorityTier::B->value,
                'notes' => 'Kebutuhan alat pengintaian amfibi & rompi pelindung.',
            ],
            [
                'customer_code' => 'CUST-007',
                'customer_name' => 'Pusziad (Pusat Zeni TNI AD)',
                'area_id' => $areaWest->id,
                'segment_id' => $seg3->id,
                'owner_id' => $tim1->id,
                'address' => 'Jl. Matraman Raya No. 157',
                'city' => 'Jakarta Timur',
                'province' => 'DKI Jakarta',
                'contact_name' => 'Kolonel Czi Farhan',
                'contact_position' => 'Dirbinlitbang Pusziad',
                'contact_phone' => '081333778899',
                'contact_email' => 'farhan@pusziad.mil.id',
                'priority_tier' => PriorityTier::B->value,
                'notes' => 'Peralatan konstruksi taktis & penjinakan bahan peledak.',
            ],
            [
                'customer_code' => 'CUST-008',
                'customer_name' => 'Pussenkav (Pusat Kesenjataan Kavaleri)',
                'area_id' => $areaCentral->id,
                'segment_id' => $seg3->id,
                'owner_id' => $tim1->id,
                'address' => 'Jl. Penyerangan No. 1',
                'city' => 'Bandung',
                'province' => 'Jawa Barat',
                'contact_name' => 'Brigjen TNI Maryono',
                'contact_position' => 'Danpussenkavkud',
                'contact_phone' => '081822889900',
                'contact_email' => 'danpussenkav@pussenkav.mil.id',
                'priority_tier' => PriorityTier::A->value,
                'notes' => 'Aksesoris & sistem perlengkapan kendaraan tempur lapis baja.',
            ],
            [
                'customer_code' => 'CUST-009',
                'customer_name' => 'Kopasgat (Komando Pasukan Gerak Cepat)',
                'area_id' => $areaWest->id,
                'segment_id' => $seg1->id,
                'owner_id' => $tim1->id,
                'address' => 'Lanud Sulaiman, Margahayu',
                'city' => 'Bandung',
                'province' => 'Jawa Barat',
                'contact_name' => 'Marsda TNI Arif',
                'contact_position' => 'Dankorpasgat',
                'contact_phone' => '081911990011',
                'contact_email' => 'dankorpasgat@kopasgat.mil.id',
                'priority_tier' => PriorityTier::A->value,
                'notes' => 'Helm tempur, parasut tactical, & kaporlap pasukan darat AU.',
            ],

            // Fikri Ardisa (FA) - Group AD / Kemhan / Kostrad / Kopassus
            [
                'customer_code' => 'CUST-010',
                'customer_name' => 'Mabesad (Mabes TNI AD)',
                'area_id' => $areaWest->id,
                'segment_id' => $seg1->id,
                'owner_id' => $tim2->id,
                'address' => 'Jl. Veteran No. 5',
                'city' => 'Jakarta Pusat',
                'province' => 'DKI Jakarta',
                'contact_name' => 'Mayjen TNI Suwandi',
                'contact_position' => 'Aslog KSAD',
                'contact_phone' => '081200112233',
                'contact_email' => 'aslog@mabesad.mil.id',
                'priority_tier' => PriorityTier::A->value,
                'notes' => 'Pengadaan skala besar perlengkapan perorangan & kaporlap AD.',
            ],
            [
                'customer_code' => 'CUST-011',
                'customer_name' => 'Kostrad (Komando Cadangan Strategis AD)',
                'area_id' => $areaWest->id,
                'segment_id' => $seg1->id,
                'owner_id' => $tim2->id,
                'address' => 'Jl. Medan Merdeka Timur No. 3',
                'city' => 'Jakarta Pusat',
                'province' => 'DKI Jakarta',
                'contact_name' => 'Kolonel Inf Rahmat',
                'contact_position' => 'Aslog Kaskostrad',
                'contact_phone' => '081311223344',
                'contact_email' => 'aslog@kostrad.mil.id',
                'priority_tier' => PriorityTier::A->value,
                'notes' => 'Seragam tactical, ransel tempur & boots lapangan Kostrad.',
            ],
            [
                'customer_code' => 'CUST-012',
                'customer_name' => 'Kopassus (Komando Pasukan Khusus)',
                'area_id' => $areaWest->id,
                'segment_id' => $seg1->id,
                'owner_id' => $tim2->id,
                'address' => 'Kandang Menjangan, Cijantung',
                'city' => 'Jakarta Timur',
                'province' => 'DKI Jakarta',
                'contact_name' => 'Kolonel Inf Hendri',
                'contact_position' => 'Aslog Danjen Kopassus',
                'contact_phone' => '081122334455',
                'contact_email' => 'aslog@kopassus.mil.id',
                'priority_tier' => PriorityTier::A->value,
                'notes' => 'Peralatan operasi khusus, rompi anti peluru & night vision.',
            ],
            [
                'customer_code' => 'CUST-013',
                'customer_name' => 'Pusbekangad (Pusat Pembekalan Angkutan AD)',
                'area_id' => $areaWest->id,
                'segment_id' => $seg1->id,
                'owner_id' => $tim2->id,
                'address' => 'Jl. Cramat Raya No. 12',
                'city' => 'Jakarta Timur',
                'province' => 'DKI Jakarta',
                'contact_name' => 'Brigjen TNI Cba Rahmat',
                'contact_position' => 'Kapusbekangad',
                'contact_phone' => '081522334455',
                'contact_email' => 'kapus@pusbekangad.mil.id',
                'priority_tier' => PriorityTier::A->value,
                'notes' => 'Pusat distribusi kaporlap & pembekalan angkutan AD.',
            ],
            [
                'customer_code' => 'CUST-014',
                'customer_name' => 'Puspalad (Pusat Peralatan TNI AD)',
                'area_id' => $areaWest->id,
                'segment_id' => $seg3->id,
                'owner_id' => $tim2->id,
                'address' => 'Jl. Ridwan Rais No. 4',
                'city' => 'Jakarta Pusat',
                'province' => 'DKI Jakarta',
                'contact_name' => 'Kolonel Cpl Agus',
                'contact_position' => 'Dirbinlitbang Puspalad',
                'contact_phone' => '081733445566',
                'contact_email' => 'agus@puspalad.mil.id',
                'priority_tier' => PriorityTier::B->value,
                'notes' => 'Suku cadang peralatan tempur & instrumen perawatan teknis.',
            ],
            [
                'customer_code' => 'CUST-015',
                'customer_name' => 'Puspenerbad (Pusat Penerbangan TNI AD)',
                'area_id' => $areaWest->id,
                'segment_id' => $seg3->id,
                'owner_id' => $tim2->id,
                'address' => 'Pondok Cabe, Pamulang',
                'city' => 'Tangerang Selatan',
                'province' => 'Banten',
                'contact_name' => 'Letkol Cpn Farhan',
                'contact_position' => 'Dirbinren Puspenerbad',
                'contact_phone' => '081255667788',
                'contact_email' => 'farhan@puspenerbad.mil.id',
                'priority_tier' => PriorityTier::B->value,
                'notes' => 'Perlengkapan penerbang heli tempur & kaporlap khusus.',
            ],
            [
                'customer_code' => 'CUST-016',
                'customer_name' => 'Paspampres (Pasukan Pengamanan Presiden)',
                'area_id' => $areaWest->id,
                'segment_id' => $seg1->id,
                'owner_id' => $tim2->id,
                'address' => 'Jl. Tanah Abang II No. 6',
                'city' => 'Jakarta Pusat',
                'province' => 'DKI Jakarta',
                'contact_name' => 'Kolonel Mar Budi',
                'contact_position' => 'Aslog Danpaspampres',
                'contact_phone' => '081366778899',
                'contact_email' => 'aslog@paspampres.mil.id',
                'priority_tier' => PriorityTier::A->value,
                'notes' => 'Set seragam tactical formal, rompi tersembunyi & earphone komunikasi.',
            ],
            [
                'customer_code' => 'CUST-017',
                'customer_name' => 'Kemhan (Kementerian Pertahanan RI)',
                'area_id' => $areaWest->id,
                'segment_id' => $seg2->id,
                'owner_id' => $tim2->id,
                'address' => 'Jl. Medan Merdeka Barat No. 13-14',
                'city' => 'Jakarta Pusat',
                'province' => 'DKI Jakarta',
                'contact_name' => 'Marsekal Muda TNI Setiawan',
                'contact_position' => 'Kabaranahan Kemhan',
                'contact_phone' => '081199887766',
                'contact_email' => 'baranahan@kemhan.go.id',
                'priority_tier' => PriorityTier::A->value,
                'notes' => 'Proyek pengadaan alpalhan strategis Baranahan & Pothan Kemhan.',
            ],

            // Wera Sauma (WS) - Group Mabes TNI / Mabes AU / Polri / Babek / Pusada
            [
                'customer_code' => 'CUST-018',
                'customer_name' => 'Mabes AU (Mabes TNI AU)',
                'area_id' => $areaWest->id,
                'segment_id' => $seg2->id,
                'owner_id' => $tim3->id,
                'address' => 'Mabesau Cilangkap',
                'city' => 'Jakarta Timur',
                'province' => 'DKI Jakarta',
                'contact_name' => 'Marsda TNI Danang',
                'contact_position' => 'Aslog KSAU',
                'contact_phone' => '081277889900',
                'contact_email' => 'aslog@tni-au.mil.id',
                'priority_tier' => PriorityTier::A->value,
                'notes' => 'Perlengkapan komunikasi penerbangan & sarana avionik AU.',
            ],
            [
                'customer_code' => 'CUST-019',
                'customer_name' => 'Kogabwilhan (Komando Gabungan Wilayah Pertahanan)',
                'area_id' => $areaWest->id,
                'segment_id' => $seg2->id,
                'owner_id' => $tim3->id,
                'address' => 'Mabes TNI Cilangkap',
                'city' => 'Jakarta Timur',
                'province' => 'DKI Jakarta',
                'contact_name' => 'Laksda TNI Bambang',
                'contact_position' => 'Aslog Kogabwilhan I',
                'contact_phone' => '081388990011',
                'contact_email' => 'aslog@kogabwilhan.mil.id',
                'priority_tier' => PriorityTier::B->value,
                'notes' => 'Sistem komunikasi posko gabungan tiga matra.',
            ],
            [
                'customer_code' => 'CUST-020',
                'customer_name' => 'Koopssus TNI (Komando Operasi Khusus)',
                'area_id' => $areaWest->id,
                'segment_id' => $seg1->id,
                'owner_id' => $tim3->id,
                'address' => 'Mabes TNI Cilangkap',
                'city' => 'Jakarta Timur',
                'province' => 'DKI Jakarta',
                'contact_name' => 'Brigjen TNI Triyono',
                'contact_position' => 'Dankoopssus TNI',
                'contact_phone' => '081199001122',
                'contact_email' => 'dankoopssus@tni.mil.id',
                'priority_tier' => PriorityTier::A->value,
                'notes' => 'Perlengkapan lapangan anti-teror tri-matra terpadu.',
            ],
            [
                'customer_code' => 'CUST-021',
                'customer_name' => 'Polri (Mabes Kepolisian Negara Republik Indonesia)',
                'area_id' => $areaWest->id,
                'segment_id' => $seg1->id,
                'owner_id' => $tim3->id,
                'address' => 'Jl. Trunojoyo No. 3, Kebayoran Baru',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'contact_name' => 'Irjen Pol Argo',
                'contact_position' => 'Aslog Kapolri',
                'contact_phone' => '081500112233',
                'contact_email' => 'slog@polri.go.id',
                'priority_tier' => PriorityTier::A->value,
                'notes' => 'Kaporlap rompi taktis, helm & perlengkapan dinas Polri.',
            ],
            [
                'customer_code' => 'CUST-022',
                'customer_name' => 'Babek TNI (Badan Pembekalan TNI)',
                'area_id' => $areaWest->id,
                'segment_id' => $seg1->id,
                'owner_id' => $tim3->id,
                'address' => 'Kawasan Babek TNI, Cilincing',
                'city' => 'Jakarta Utara',
                'province' => 'DKI Jakarta',
                'contact_name' => 'Kolonel Cba Maryono',
                'contact_position' => 'Kababek TNI',
                'contact_phone' => '081711223344',
                'contact_email' => 'kababek@tni.mil.id',
                'priority_tier' => PriorityTier::A->value,
                'notes' => 'Pengadaan distribusi kaporlap nasional TNI.',
            ],
            [
                'customer_code' => 'CUST-023',
                'customer_name' => 'Mabes TNI (Mabes Tentara Nasional Indonesia)',
                'area_id' => $areaWest->id,
                'segment_id' => $seg2->id,
                'owner_id' => $tim3->id,
                'address' => 'Cilangkap',
                'city' => 'Jakarta Timur',
                'province' => 'DKI Jakarta',
                'contact_name' => 'Mayjen TNI Rahmat',
                'contact_position' => 'Aslog Panglima TNI',
                'contact_phone' => '081222334455',
                'contact_email' => 'aslog@tni.mil.id',
                'priority_tier' => PriorityTier::A->value,
                'notes' => 'Proyek strategis Slog, Srenum & Satkomlek Mabes TNI.',
            ],
            [
                'customer_code' => 'CUST-024',
                'customer_name' => 'Pusada TNI (Pusat Pengadaan TNI)',
                'area_id' => $areaWest->id,
                'segment_id' => $seg3->id,
                'owner_id' => $tim3->id,
                'address' => 'Mabes TNI Cilangkap',
                'city' => 'Jakarta Timur',
                'province' => 'DKI Jakarta',
                'contact_name' => 'Marsma TNI Farhan',
                'contact_position' => 'Kapusada TNI',
                'contact_phone' => '081333445566',
                'contact_email' => 'pusada@tni.mil.id',
                'priority_tier' => PriorityTier::A->value,
                'notes' => 'Lembaga pengadaan barang & jasa terpusat Mabes TNI.',
            ],
        ];

        $customers = [];
        foreach ($custsData as $c) {
            $c['is_active'] = true;
            $c['created_by'] = $admin->id;
            $customers[] = Customer::create($c);
        }

        // Extra customers needed for weekly schedule
        Customer::firstOrCreate(
            ['customer_code' => 'CUST-025'],
            [
                'customer_name' => 'Akademi TNI',
                'area_id' => $areaWest->id,
                'segment_id' => $seg1->id,
                'owner_id' => $directorProyek->id,
                'address' => 'Jl. Raya Magelang, Magelang',
                'city' => 'Magelang',
                'province' => 'Jawa Tengah',
                'contact_name' => 'Komandan Akademi TNI',
                'contact_phone' => '081200001111',
                'priority_tier' => PriorityTier::A->value,
                'notes' => 'Akademi TNI - pendidikan perwira TNI.',
                'is_active' => true,
                'created_by' => $admin->id,
            ]
        );

        Customer::firstOrCreate(
            ['customer_code' => 'CUST-026'],
            [
                'customer_name' => 'Disaero Mabes TNI AU',
                'area_id' => $areaWest->id,
                'segment_id' => $seg2->id,
                'owner_id' => $tim2->id,
                'address' => 'Mabes TNI AU, Cilangkap',
                'city' => 'Jakarta Timur',
                'province' => 'DKI Jakarta',
                'contact_name' => 'Mayor Tirta',
                'contact_phone' => '081200002222',
                'priority_tier' => PriorityTier::B->value,
                'notes' => 'Direktorat Aeronautika Mabes TNI AU.',
                'is_active' => true,
                'created_by' => $admin->id,
            ]
        );


        // 5. Visit Plans (Current Month)
$now = Carbon::now();
$currentMonthStr = $now->format('Y-m');

// Helper to fetch customer by name (supports partial match & safe fallback)
$getCustomer = function(string $name) {
    $cust = \App\Models\Customer::where('customer_name', $name)
        ->orWhere('customer_name', 'like', $name . '%')
        ->orWhere('customer_name', 'like', '%' . $name . '%')
        ->first();

    if (!$cust) {
        $firstWord = explode(' ', trim($name))[0] ?? $name;
        $cust = \App\Models\Customer::where('customer_name', 'like', '%' . $firstWord . '%')->first();
    }

    return $cust ?: \App\Models\Customer::first();
};

// 21 September Visits
$plan21 = VisitPlan::create([
    'plan_number' => 'VP-' . $now->format('Ym') . '-021',
    'customer_id' => $getCustomer('Akademi TNI')->id,
    'area_id' => $getCustomer('Akademi TNI')->area_id,
    'owner_id' => $directorProyek->id, // WR as owner
    'plan_month' => $currentMonthStr,
    'planned_date' => '2026-09-21',
    'start_time' => '09:00:00',
    'end_time' => '11:00:00',
    'activity_type' => ActivityType::SALES_VISIT->value,
    'priority' => PriorityLevel::HIGH->value,
    'monthly_objective' => 'Visit Akademi TNI for product presentation.',
    'specific_objective' => 'Present tactical gear to WR, WS, FA, GP.',
    'resource_notes' => 'Bring sample equipment and brochure.',
    'status' => PlanStatus::COMPLETED->value,
    'actual_start_at' => $now->copy()->setDate(2026,9,21)->setTime(9,0,0),
    'actual_end_at' => $now->copy()->setDate(2026,9,21)->setTime(11,0,0),
    'started_by' => $directorProyek->id,
    'completed_at' => $now->copy()->setDate(2026,9,21)->setTime(11,0,0),
    'created_by' => $admin->id,
]);
$plan21->members()->attach([$directorProyek->id, $tim3->id, $tim2->id, $tim1->id]);

// 22 September Visits
// Kemhan
$plan22_kemhan = VisitPlan::create([
    'plan_number' => 'VP-' . $now->format('Ym') . '-022K',
    'customer_id' => $getCustomer('Kemhan')->id,
    'area_id' => $getCustomer('Kemhan')->area_id,
    'owner_id' => $directorProyek->id,
    'plan_month' => $currentMonthStr,
    'planned_date' => '2026-09-22',
    'start_time' => '09:00:00',
    'end_time' => '11:30:00',
    'activity_type' => ActivityType::SALES_VISIT->value,
    'priority' => PriorityLevel::HIGH->value,
    'monthly_objective' => 'Discuss procurement with Kemhan.',
    'specific_objective' => 'Present to WR and WS, involve GP.',
    'resource_notes' => 'Bring product catalog.',
    'status' => PlanStatus::COMPLETED->value,
    'actual_start_at' => $now->copy()->setDate(2026,9,22)->setTime(9,0,0),
    'actual_end_at' => $now->copy()->setDate(2026,9,22)->setTime(11,30,0),
    'started_by' => $directorProyek->id,
    'completed_at' => $now->copy()->setDate(2026,9,22)->setTime(11,30,0),
    'created_by' => $admin->id,
]);
$plan22_kemhan->members()->attach([$directorProyek->id, $tim3->id, $tim1->id]);

// Pusada Mabes TNI
$plan22_pusada = VisitPlan::create([
    'plan_number' => 'VP-' . $now->format('Ym') . '-022P',
    'customer_id' => $getCustomer('Pusada Mabes TNI')->id,
    'area_id' => $getCustomer('Pusada Mabes TNI')->area_id,
    'owner_id' => $admin->id, // LN as owner
    'plan_month' => $currentMonthStr,
    'planned_date' => '2026-09-22',
    'start_time' => '13:00:00',
    'end_time' => '15:00:00',
    'activity_type' => ActivityType::SALES_VISIT->value,
    'priority' => PriorityLevel::MEDIUM->value,
    'monthly_objective' => 'Meeting Pusada Mabes TNI.',
    'specific_objective' => 'Discuss HUT TNI and satpamwal procurement.',
    'resource_notes' => 'Bring briefing documents.',
    'status' => PlanStatus::COMPLETED->value,
    'actual_start_at' => $now->copy()->setDate(2026,9,22)->setTime(13,0,0),
    'actual_end_at' => $now->copy()->setDate(2026,9,22)->setTime(15,0,0),
    'started_by' => $admin->id,
    'completed_at' => $now->copy()->setDate(2026,9,22)->setTime(15,0,0),
    'created_by' => $admin->id,
]);
$plan22_pusada->members()->attach([$admin->id, $tim2->id]);

// Disaero MAbes TNI AU
$plan22_disaero = VisitPlan::create([
    'plan_number' => 'VP-' . $now->format('Ym') . '-022D',
    'customer_id' => $getCustomer('Disaero MAbes TNI AU')->id,
    'area_id' => $getCustomer('Disaero MAbes TNI AU')->area_id,
    'owner_id' => $admin->id, // LN as owner
    'plan_month' => $currentMonthStr,
    'planned_date' => '2026-09-22',
    'start_time' => '15:30:00',
    'end_time' => '17:30:00',
    'activity_type' => ActivityType::SALES_VISIT->value,
    'priority' => PriorityLevel::MEDIUM->value,
    'monthly_objective' => 'Meeting Disaero TNI AU.',
    'specific_objective' => 'Discuss hangar needs and procurement.',
    'resource_notes' => 'Bring technical brochure.',
    'status' => PlanStatus::COMPLETED->value,
    'actual_start_at' => $now->copy()->setDate(2026,9,22)->setTime(15,30,0),
    'actual_end_at' => $now->copy()->setDate(2026,9,22)->setTime(17,30,0),
    'started_by' => $admin->id,
    'completed_at' => $now->copy()->setDate(2026,9,22)->setTime(17,30,0),
    'created_by' => $admin->id,
]);
$plan22_disaero->members()->attach([$admin->id, $tim2->id]);

// 23 September Visits
// Akademi TNI
$plan23_akademi = VisitPlan::create([
    'plan_number' => 'VP-' . $now->format('Ym') . '-023A',
    'customer_id' => $getCustomer('Akademi TNI')->id,
    'area_id' => $getCustomer('Akademi TNI')->area_id,
    'owner_id' => $tim3->id, // WS as owner
    'plan_month' => $currentMonthStr,
    'planned_date' => '2026-09-23',
    'start_time' => '09:00:00',
    'end_time' => '11:00:00',
    'activity_type' => ActivityType::SALES_VISIT->value,
    'priority' => PriorityLevel::MEDIUM->value,
    'monthly_objective' => 'Follow‑up meeting at Akademi TNI.',
    'specific_objective' => 'Discuss outcomes with WS and FA.',
    'resource_notes' => 'Bring meeting minutes.',
    'status' => PlanStatus::COMPLETED->value,
    'actual_start_at' => $now->copy()->setDate(2026,9,23)->setTime(9,0,0),
    'actual_end_at' => $now->copy()->setDate(2026,9,23)->setTime(11,0,0),
    'started_by' => $tim3->id,
    'completed_at' => $now->copy()->setDate(2026,9,23)->setTime(11,0,0),
    'created_by' => $admin->id,
]);
$plan23_akademi->members()->attach([$tim3->id, $tim2->id]);

// Kemhan
$plan23_kemhan = VisitPlan::create([
    'plan_number' => 'VP-' . $now->format('Ym') . '-023K',
    'customer_id' => $getCustomer('Kemhan')->id,
    'area_id' => $getCustomer('Kemhan')->area_id,
    'owner_id' => $admin->id, // LN as owner
    'plan_month' => $currentMonthStr,
    'planned_date' => '2026-09-23',
    'start_time' => '13:00:00',
    'end_time' => '15:00:00',
    'activity_type' => ActivityType::SALES_VISIT->value,
    'priority' => PriorityLevel::MEDIUM->value,
    'monthly_objective' => 'Kemhan visit.',
    'specific_objective' => 'Discuss with LN and GP.',
    'resource_notes' => 'Prepare technical dossier.',
    'status' => PlanStatus::COMPLETED->value,
    'actual_start_at' => $now->copy()->setDate(2026,9,23)->setTime(13,0,0),
    'actual_end_at' => $now->copy()->setDate(2026,9,23)->setTime(15,0,0),
    'started_by' => $admin->id,
    'completed_at' => $now->copy()->setDate(2026,9,23)->setTime(15,0,0),
    'created_by' => $admin->id,
]);
$plan23_kemhan->members()->attach([$admin->id, $tim1->id]);

// Pusziad
$plan23_pusziad = VisitPlan::create([
    'plan_number' => 'VP-' . $now->format('Ym') . '-023Z',
    'customer_id' => $getCustomer('Pusziad')->id,
    'area_id' => $getCustomer('Pusziad')->area_id,
    'owner_id' => $admin->id, // LN as owner
    'plan_month' => $currentMonthStr,
    'planned_date' => '2026-09-23',
    'start_time' => '15:30:00',
    'end_time' => '17:30:00',
    'activity_type' => ActivityType::SALES_VISIT->value,
    'priority' => PriorityLevel::MEDIUM->value,
    'monthly_objective' => 'Pusziad visit.',
    'specific_objective' => 'Coordinate with LN and GP.',
    'resource_notes' => 'Bring samples.',
    'status' => PlanStatus::COMPLETED->value,
    'actual_start_at' => $now->copy()->setDate(2026,9,23)->setTime(15,30,0),
    'actual_end_at' => $now->copy()->setDate(2026,9,23)->setTime(17,30,0),
    'started_by' => $admin->id,
    'completed_at' => $now->copy()->setDate(2026,9,23)->setTime(17,30,0),
    'created_by' => $admin->id,
]);
$plan23_pusziad->members()->attach([$admin->id, $tim1->id]);

// 24 September Visit - Akademi TNI
$plan24_akademi = VisitPlan::create([
    'plan_number' => 'VP-' . $now->format('Ym') . '-024A',
    'customer_id' => $getCustomer('Akademi TNI')->id,
    'area_id' => $getCustomer('Akademi TNI')->area_id,
    'owner_id' => $tim3->id,
    'plan_month' => $currentMonthStr,
    'planned_date' => '2026-09-24',
    'start_time' => '09:00:00',
    'end_time' => '11:00:00',
    'activity_type' => ActivityType::SALES_VISIT->value,
    'priority' => PriorityLevel::MEDIUM->value,
    'monthly_objective' => 'Final follow‑up at Akademi TNI.',
    'specific_objective' => 'Engage WS and GP.',
    'resource_notes' => 'Prepare summary deck.',
    'status' => PlanStatus::PLANNED->value,
    'created_by' => $admin->id,
]);
$plan24_akademi->members()->attach([$tim3->id, $tim1->id]);

// Visit Reports for 22 September (Pusada Mabes TNI)
$report22_pusada = VisitReport::create([
    'report_number' => 'VR-' . $now->format('Ym') . '-022P',
    'visit_plan_id' => $plan22_pusada->id,
    'actual_start_at' => $plan22_pusada->actual_start_at,
    'actual_end_at' => $plan22_pusada->actual_end_at,
    'outcome_summary' => 'Pusada Mabes TNI: Pelaksanaan HUT TNI kemungkinan di Mabes. Koordinasi pengadaan set Satpamwal melalui KP atau KD.',
    'outcome_type' => OutcomeType::PROPOSAL->value,
    'engagement_score' => 4,
    'attendance_summary' => 'FA (Fikri Ardisa) dan LN (Lilla Nur) hadir bersama Kapten Adi & Serma Adhy.',
    'next_step_summary' => 'Koordinasi dengan tim SMD dan Produksi terkait pembuatan pakaian nubika.',
    'follow_up_required' => true,
    'submitted_by' => $tim2->id,
    'submitted_at' => $now->copy()->setDate(2026,9,22)->setTime(17,30,0),
    'submit_status' => SubmitStatus::SUBMITTED->value,
    'created_by' => $admin->id,
]);

// Visit Reports for 22 September (Disaero MAbes TNI AU)
$report22_disaero = VisitReport::create([
    'report_number' => 'VR-' . $now->format('Ym') . '-022D',
    'visit_plan_id' => $plan22_disaero->id,
    'actual_start_at' => $plan22_disaero->actual_start_at,
    'actual_end_at' => $plan22_disaero->actual_end_at,
    'outcome_summary' => 'Disaero MAbes TNI AU: Tidak ada kebutuhan hanggar tahan peluru di Timika saat ini.',
    'outcome_type' => OutcomeType::NO_CHANGE->value,
    'engagement_score' => 3,
    'attendance_summary' => 'FA dan LN bertemu Mayor Tirta.',
    'next_step_summary' => 'FU terkait proyek litbang pos jaga tahan peluru.',
    'follow_up_required' => true,
    'submitted_by' => $tim2->id,
    'submitted_at' => $now->copy()->setDate(2026,9,22)->setTime(17,45,0),
    'submit_status' => SubmitStatus::SUBMITTED->value,
    'created_by' => $admin->id,
]);

// Visit Reports for 23 September (Kemhan)
$report23_kemhan = VisitReport::create([
    'report_number' => 'VR-' . $now->format('Ym') . '-023K',
    'visit_plan_id' => $plan23_kemhan->id,
    'actual_start_at' => $plan23_kemhan->actual_start_at,
    'actual_end_at' => $plan23_kemhan->actual_end_at,
    'outcome_summary' => 'Kemhan meeting outcome placeholder.',
    'outcome_type' => OutcomeType::PROPOSAL->value,
    'engagement_score' => 4,
    'attendance_summary' => 'LN and GP attended.',
    'next_step_summary' => 'Further discussion pending.',
    'follow_up_required' => true,
    'submitted_by' => $tim2->id,
    'submitted_at' => $now->copy()->setDate(2026,9,23)->setTime(15,30,0),
    'submit_status' => SubmitStatus::SUBMITTED->value,
    'created_by' => $admin->id,
]);

// Visit Reports for 23 September (Pusziad)
$report23_pusziad = VisitReport::create([
    'report_number' => 'VR-' . $now->format('Ym') . '-023Z',
    'visit_plan_id' => $plan23_pusziad->id,
    'actual_start_at' => $plan23_pusziad->actual_start_at,
    'actual_end_at' => $plan23_pusziad->actual_end_at,
    'outcome_summary' => 'Pusziad meeting outcome placeholder.',
    'outcome_type' => OutcomeType::PROPOSAL->value,
    'engagement_score' => 4,
    'attendance_summary' => 'LN and GP attended.',
    'next_step_summary' => 'Follow‑up actions defined.',
    'follow_up_required' => true,
    'submitted_by' => $tim2->id,
    'submitted_at' => $now->copy()->setDate(2026,9,23)->setTime(17,45,0),
    'submit_status' => SubmitStatus::SUBMITTED->value,
    'created_by' => $admin->id,
]);

// (End of custom schedule)
        $dirInput2 = DirectorInput::create([
            'visit_report_id' => $report23_pusziad->id,
            'visit_plan_id' => $plan23_pusziad->id,
            'customer_id' => $customers[0]->id,
            'area_id' => $customers[0]->area_id,
            'topic' => 'Penawaran Program Cross-Selling Aksesori Kendaraan Armada',
            'input_type' => DirectorInputType::SUGGESTION->value,
            'direction_text' => 'Tawarkan paket lampu sorot LED tactical untuk unit patroli Armada II karena kepuasan mereka terhadap produk winch tinggi.',
            'assigned_to' => $tim2->id,
            'priority' => PriorityLevel::MEDIUM->value,
            'due_date' => $now->copy()->addDays(5)->format('Y-m-d'),
            'status' => DirectorInputStatus::OPEN->value,
            'created_by' => $director->id,
        ]);

        // 6. App Settings
        AppSetting::create([
            'setting_key' => 'COMPANY_NAME',
            'setting_value' => 'PT PERSADA AMAN SENTOSA (PASTAC)',
            'description' => 'Nama perusahaan penyedia solusi perlengkapan pertahanan.',
            'is_active' => true,
        ]);

        AppSetting::create([
            'setting_key' => 'ALLOW_GOOGLE_LOGIN',
            'setting_value' => 'true',
            'description' => 'Izinkan pengguna masuk dengan akun Google OAuth.',
            'is_active' => true,
        ]);

        // 7. Audit Log initial
        AuditLog::create([
            'actor_id' => $admin->id,
            'action' => 'SYSTEM_INITIALIZED',
            'entity_type' => 'SYSTEM',
            'entity_id' => null,
            'metadata' => ['note' => 'Database SAMARA berhasil di-seed dengan data master fiktif.'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Seeder Script',
        ]);
    }
}
