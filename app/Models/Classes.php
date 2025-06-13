<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    /**
     * Properti $fillable menentukan kolom mana saja yang boleh diisi
     * secara massal. Dalam hal ini, hanya kolom 'name'.
     */
    protected $fillable = ['name'];

    /**
     * Mendefinisikan relasi kebalikannya: satu Kelas bisa memiliki banyak User (siswa).
     * Ini adalah relasi "hasMany".
     */
    public function users()
    {
        return $this->hasMany(User::class, 'class_id');
    }
}
