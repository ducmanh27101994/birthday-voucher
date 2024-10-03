<?php

namespace Fmcpay\BirthdayVoucher\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Voucher extends Model
{
    use HasFactory;

    protected $table = 'vouchers';

    protected $fillable = [
        'user_id',
        'amount',
        'expires_at',
        'code',
        'is_used'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
