<?php

use Illuminate\Support\Facades\Route;
use Fmcpay\BirthdayVoucher\Http\Controllers\VoucherController;

Route::post('/indexVoucherUser', [VoucherController::class, 'indexVoucherUser']); //Danh sách tất cả voucher theo user
Route::post('/vouchers/use', [VoucherController::class, 'useVoucher']); //Sử dụng voucher




