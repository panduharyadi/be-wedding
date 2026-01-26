<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateTemplate extends Model
{
    protected $primaryKey = 'pk_certificate_template_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $appends = ['id'];

    protected $fillable = [
        'owner_id',
        'name',
        'background_path',
        'fields',
    ];

    protected $casts = [
        'fields' => 'array',
    ];

    public function getIdAttribute()
    {
        return $this->pk_certificate_template_id;
    }

    public function owner() {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function certificates() {
        return $this->hasMany(Certificate::class, 'template_id', 'pk_certificate_template_id');
    }
}
