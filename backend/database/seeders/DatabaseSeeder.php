<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Users ──
        $manager = User::updateOrCreate(
            ['username' => 'manager'],
            [
                'name'       => 'Budi Manajer',
                'email'      => 'manager@kpi.app',
                'password'   => Hash::make('password123'),
                'role'       => 'manager',
                'department' => 'Engineering',
                'position'   => 'Engineering Manager',
                'is_active'  => true,
            ]
        );

        $andi = User::updateOrCreate(
            ['username' => 'andi'],
            [
                'name'       => 'Andi Pratama',
                'email'      => 'andi@kpi.app',
                'password'   => Hash::make('password123'),
                'role'       => 'employee',
                'department' => 'Engineering',
                'position'   => 'Backend Developer',
                'is_active'  => true,
            ]
        );

        $sari = User::updateOrCreate(
            ['username' => 'sari'],
            [
                'name'       => 'Sari Lestari',
                'email'      => 'sari@kpi.app',
                'password'   => Hash::make('password123'),
                'role'       => 'employee',
                'department' => 'Sales',
                'position'   => 'Sales Executive',
                'is_active'  => true,
            ]
        );

        $budi = User::updateOrCreate(
            ['username' => 'budi'],
            [
                'name'       => 'Budi Santoso',
                'email'      => 'budi@kpi.app',
                'password'   => Hash::make('password123'),
                'role'       => 'employee',
                'department' => 'Engineering',
                'position'   => 'QA Engineer',
                'is_active'  => true,
            ]
        );

        $dewi = User::updateOrCreate(
            ['username' => 'dewi'],
            [
                'name'       => 'Dewi Rahayu',
                'email'      => 'dewi@kpi.app',
                'password'   => Hash::make('password123'),
                'role'       => 'employee',
                'department' => 'Support',
                'position'   => 'Customer Support',
                'is_active'  => true,
            ]
        );

        // ── Seed Firestore Notifications ──
        $this->seedFirestoreNotifications([$andi, $sari, $budi, $dewi]);
    }

    private function seedFirestoreNotifications(array $employees): void
    {
        try {
            $firestore = new \Google\Cloud\Firestore\FirestoreClient([
                'projectId' => env('GCP_PROJECT_ID'),
                'transport' => 'rest',
            ]);
            $collection = $firestore->collection('notifications');

            // Cek apakah sudah pernah di-seed (hindari duplikat)
            $existing = $collection->where('type', '=', 'kpi_assigned')->limit(1)->documents();
            foreach ($existing as $doc) {
                if ($doc->exists()) {
                    return; // Sudah ada, skip
                }
            }

            $notifications = [
                [
                    'type'  => 'kpi_assigned',
                    'title' => 'KPI Baru Ditetapkan',
                    'body'  => 'Manajer telah menetapkan bobot KPI bulan ini untuk Anda. Segera cek dan mulai kerjakan target Anda!',
                ],
                [
                    'type'  => 'target_reminder',
                    'title' => 'Pengingat Target Bulanan',
                    'body'  => 'Periode evaluasi bulan ini sudah berjalan. Jangan lupa update progres Anda secara berkala.',
                ],
                [
                    'type'  => 'progress_low',
                    'title' => 'Progres Masih Rendah',
                    'body'  => 'Progres target Anda masih di bawah 50%. Deadline semakin dekat, segera update progres!',
                ],
            ];

            foreach ($employees as $emp) {
                foreach ($notifications as $i => $notif) {
                    $collection->add([
                        'recipientId' => $emp->id,
                        'senderId'    => null,
                        'type'        => $notif['type'],
                        'title'       => $notif['title'],
                        'body'        => $notif['body'],
                        'data'        => ['screen' => 'home'],
                        'isRead'      => ($i === 1), // Salah satu sudah dibaca, sisanya belum
                        'readAt'      => null,
                        'createdAt'   => new \Google\Cloud\Core\Timestamp(new \DateTime("-" . ($i * 2 + 1) . " hours")),
                    ]);
                }
            }

            echo "Firestore notifications seeded successfully.\n";
        } catch (\Exception $e) {
            echo "Firestore notification seeding skipped: " . $e->getMessage() . "\n";
        }
    }
}
