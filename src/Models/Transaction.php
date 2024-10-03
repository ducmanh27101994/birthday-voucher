<?php

namespace Fmcpay\BirthdayVoucher\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'user_id',
        'amount',
        'previous_balance',
        'new_balance',
        'currency',
        'voucher_code',
        'created_at',
    ];
}
