<?php

namespace App\Models; // Sesuaikan namespace jika Anda menggunakan struktur folder berbeda (misal App\)

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Opsional, jika Anda menggunakan factories

class EmotionRecord extends Model
{
    // use HasFactory; // Uncomment jika Anda menggunakan model factories

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'emotion_records';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'detection_timestamp',
        'dominant_emotion',
        'happines_score',
        'sadness_score',
        'anger_score',
        'fear_score',
        'disgust_score',
        'surprise_score',
        'neutral_score',
        'age',
        'gender',
        'gender_probability',
    ];

    /**
     * Atribut yang harus di-cast ke tipe data asli.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'detection_timestamp' => 'datetime',
        'happines_score' => 'float', // Jika typo diperbaiki. Jika tidak, ganti menjadi 'happines_score'
        'sadness_score' => 'float',
        'anger_score' => 'float',
        'fear_score' => 'float',
        'disgust_score' => 'float',
        'surprise_score' => 'float',
        'neutral_score' => 'float',
        'age' => 'integer',
        'gender_probability' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Mendapatkan pengguna (user) yang memiliki catatan emosi ini.
     */
    public function user()
    {
        // Pastikan model User ada di App\Models\User atau App\User
        return $this->belongsTo(User::class, 'user_id');
    }
}
