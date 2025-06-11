<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmotionRecord;
use App\Models\User;
use Carbon\Carbon;

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
            if (empty($users)) return;
        }

        $this->command->info('Memulai EmotionRecordSeeder untuk user ID: ' . implode(', ', $users));

        $possibleEmotions = ['happy', 'sad', 'neutral', 'angry', 'surprised', 'fearful', 'disgusted'];
        $genders = ['male', 'female'];

        // Tentukan rentang tanggal spesifik untuk data dummy (1 Juni - 7 Juni 2025)
        $startDate = Carbon::create(2025, 6, 1);
        $numberOfDaysToSeed = 7;

        foreach ($users as $userId) {
            $this->command->info("Membuat data untuk user ID: {$userId}");
            for ($dayOffset = 0; $dayOffset < $numberOfDaysToSeed; $dayOffset++) {
                $currentLoopDate = $startDate->copy()->addDays($dayOffset);

                $recordsPerDay = rand(2, 4); // Buat 2-4 record per hari
                $this->command->info("   -- Tanggal: {$currentLoopDate->toDateString()}, {$recordsPerDay} records");

                for ($i = 0; $i < $recordsPerDay; $i++) {
                    $dominantEmotion = 'happy'; // Default ke happy
                    // 70% kemungkinan happy menjadi emosi dominan
                    if (rand(1, 100) > 30) {
                        $dominantEmotion = 'happy';
                    } else {
                        // Jika bukan happy, pilih emosi lain secara acak
                        $otherEmotions = array_diff($possibleEmotions, ['happy']);
                        $dominantEmotion = $otherEmotions[array_rand($otherEmotions)];
                    }

                    // Buat timestamp acak dalam jam kerja (08:00 - 22:00) pada $currentLoopDate
                    $detectionTimestamp = $currentLoopDate->copy()
                                                ->setHour(rand(8, 22))
                                                ->setMinute(rand(0, 59))
                                                ->setSecond(rand(0, 59));

                    $expressions = [];
                    foreach ($possibleEmotions as $emotion) {
                        $score = round(mt_rand(0, 50) / 100, 4); // Skor default lebih rendah
                        if ($emotion === $dominantEmotion) {
                            if ($emotion === 'happy') {
                                $score = round(mt_rand(90, 100) / 100, 4); // Happy dominan: skor sangat tinggi
                            } else {
                                $score = round(mt_rand(65, 100) / 100, 4); // Emosi lain dominan: skor tinggi
                            }
                        } elseif ($emotion === 'happy') {
                            // Jika happy bukan dominan, tetap berikan skor yang lumayan
                            $score = round(mt_rand(35, 75) / 100, 4);
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
                    ]);
                }
            }
        }
        $this->command->info('EmotionRecordSeeder berhasil dijalankan. Data dummy dengan dominasi "happy" telah dibuat.');
    }
}
