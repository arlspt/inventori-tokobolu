<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserModulePermission extends Model
{
    protected $table = 'user_module_permissions';

    protected $fillable = [
        'user_id',
        'modul',
        'dapat_akses',
    ];

    protected $casts = [
        'dapat_akses' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
