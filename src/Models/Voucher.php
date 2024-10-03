<?php

namespace Fmcpay\BirthdayVoucher\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Voucher extends Model
{
    use HasFactory;

    protected $table = 'vouchers';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'amount',
        'expires_at',
        'code',
        'is_used',
        'currency',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
