<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\URL; // Dikembalikan sesuai kode asli


class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name', 'email', 'password', 'photo',
        'role', // Perubahan: 'role' ditambahkan
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Bagian ini TIDAK DIUBAH sesuai permintaan Anda.
     */
    public function getPhotoUrlAttribute()
    {
    if ($this->photo) {
        // PERUBAHAN KUNCI DI SINI:
        // Buat URL langsung ke folder 'profile' di dalam 'public'
        return URL::asset('profile/' . $this->photo);
    }
    return null;
    }


    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key-value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        // Perubahan: Menambahkan 'role' ke dalam payload JWT
        return [
            'role' => $this->role,
        ];
    }

    // --- FUNGSI BANTUAN UNTUK ROLE ---

    /**
     * Cek apakah user adalah admin.
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Cek apakah user adalah pengajar.
     * @return bool
     */
    public function isPengajar(): bool
    {
        return $this->role === 'pengajar';
    }

    /**
     * Cek apakah user adalah murid.
     * @return bool
     */
    public function isMurid(): bool
    {
        return $this->role === 'murid';
    }
}