<?php

use Illuminate\Support\Facades\Route;
use Fmcpay\BirthdayVoucher\Http\Controllers\VoucherController;

Route::post('/indexVoucherUser', [VoucherController::class, 'indexVoucherUser']); //Danh sách tất cả voucher theo user
Route::post('/vouchers/use', [VoucherController::class, 'useVoucher']); //Sử dụng voucher


Route::post('/listAllVoucher', [VoucherController::class, 'listAllVoucher']);
Route::post('/createVoucher', [VoucherController::class, 'createVoucher']);
Route::post('/deleteVoucher', [VoucherController::class, 'deleteVoucher']);
Route::post('/updateVoucher', [VoucherController::class, 'updateVoucher']);

