<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'user_id',
        'paket_id',
        'transaction_id',
        'template_id',
        'certificate_number',
        'file_path',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function paket() {
        return $this->belongsTo(Paket::class);
    }

    public function template() {
        return $this->belongsTo(CertificateTemplate::class);
    }
}
