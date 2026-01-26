<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $primaryKey = 'pk_certificate_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $appends = ['id'];

    protected $fillable = [
        'user_id',
        'paket_id',
        'transaction_id',
        'template_id',
        'certificate_number',
        'file_path',
    ];

    public function getIdAttribute()
    {
        return $this->pk_certificate_id;
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function paket() {
        return $this->belongsTo(Paket::class, 'paket_id', 'pk_paket_id');
    }

    public function template() {
        return $this->belongsTo(CertificateTemplate::class, 'template_id', 'pk_certificate_template_id');
    }
}
