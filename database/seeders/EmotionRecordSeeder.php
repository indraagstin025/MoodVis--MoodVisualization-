<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmotionRecord;
use App\Models\User;
use Carbon\Carbon;
// use Illuminate\Support\Facades\DB; // Tidak digunakan jika pakai Eloquent create

class EmotionRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Opsional: Kosongkan tabel emotion_records sebelum seeding jika diperlukan
        // EmotionRecord::truncate(); // Hati-hati, ini menghapus semua data di tabel ini

        $users = User::take(2)->pluck('id')->toArray();

        if (empty($users)) {
            $this->command->error('Tidak ada user ditemukan di database. Mohon buat user terlebih dahulu atau jalankan UserSeeder.');
            // Contoh jika Anda ingin membuat user default jika tidak ada
            // User::factory()->create(['id' => 1, 'name' => 'Test User 1', 'email' => 'user1@example.com', 'password' => bcrypt('password')]);
            // $users = [1]; // Gunakan user ID yang baru dibuat
            // Jika masih kosong, hentikan seeder
            if (empty($users)) return;
        }

        $this->command->info('Memulai EmotionRecordSeeder untuk user ID: ' . implode(', ', $users));

        $possibleEmotions = ['happy', 'sad', 'neutral', 'angry', 'surprised', 'fearful', 'disgusted'];
        $genders = ['male', 'female'];

        // Tentukan rentang tanggal spesifik untuk data dummy
        $startDate = Carbon::create(2025, 5, 1); // 1 Mei 2025
        $numberOfDaysToSeed = 7; // Untuk 7 hari (1 s.d. 7 Mei)

        foreach ($users as $userId) {
            $this->command->info("Membuat data untuk user ID: {$userId}");
            for ($dayOffset = 0; $dayOffset < $numberOfDaysToSeed; $dayOffset++) {
                // Tanggal saat ini dalam loop (mulai dari $startDate, lalu +1 hari, dst.)
                $currentLoopDate = $startDate->copy()->addDays($dayOffset);

                $recordsPerDay = rand(2, 4); // Buat 2-4 record per hari
                $this->command->info("  -- Tanggal: {$currentLoopDate->toDateString()}, {$recordsPerDay} records");

                for ($i = 0; $i < $recordsPerDay; $i++) {
                    $dominantEmotion = $possibleEmotions[array_rand($possibleEmotions)];
                    // Buat timestamp acak dalam jam kerja (08:00 - 22:00) pada $currentLoopDate
                    $detectionTimestamp = $currentLoopDate->copy()
                                            ->setHour(rand(8, 22))
                                            ->setMinute(rand(0, 59))
                                            ->setSecond(rand(0, 59));

                    $expressions = [];
                    foreach ($possibleEmotions as $emotion) {
                        $score = round(mt_rand(0, 100) / 100, 4);
                        if ($emotion === $dominantEmotion) {
                            $score = round(mt_rand(65, 100) / 100, 4); // Skor dominan lebih tinggi
                        }
                        $expressions[$emotion . '_score'] = $score;
                    }

                    EmotionRecord::create([
                        'user_id' => $userId,
                        'detection_timestamp' => $detectionTimestamp,
                        'dominant_emotion' => $dominantEmotion,
                        'happiness_score' => $expressions['happy_score'],
                        'sadness_score'   => $expressions['sad_score'],
                        'anger_score'     => $expressions['angry_score'],
                        'fear_score'      => $expressions['fearful_score'],
                        'disgust_score'   => $expressions['disgusted_score'],
                        'surprise_score'  => $expressions['surprised_score'],
                        'neutral_score'   => $expressions['neutral_score'],
                        'gender' => $genders[array_rand($genders)],
                        'gender_probability' => round(mt_rand(75, 100) / 100, 4),
                        // created_at dan updated_at akan diisi otomatis oleh Eloquent
                        // jika Anda ingin mengontrolnya secara manual (misal samakan dengan detection_timestamp):
                        // 'created_at' => $detectionTimestamp,
                        // 'updated_at' => $detectionTimestamp,
                    ]);
                }
            }
        }
        $this->command->info('EmotionRecordSeeder berhasil dijalankan. Data dummy untuk 1-7 Mei 2025 telah dibuat.');
    }
}
