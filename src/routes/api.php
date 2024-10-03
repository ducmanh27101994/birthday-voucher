<?php

use Illuminate\Support\Facades\Route;
use Fmcpay\BirthdayVoucher\Http\Controllers\VoucherController;
use Fmcpay\BirthdayVoucher\Http\Controllers\AdminVoucherController;

Route::post('/indexVoucherUser', [VoucherController::class, 'indexVoucherUser']); //Danh sách tất cả voucher theo user
Route::post('/vouchers/use', [VoucherController::class, 'useVoucher']); //Sử dụng voucher

Route::middleware('admin')->group(function () {
    Route::post('/listAllVoucher', [AdminVoucherController::class, 'listAllVoucher']);
    Route::post('/createVoucher', [AdminVoucherController::class, 'createVoucher']);
    Route::post('/deleteVoucher', [AdminVoucherController::class, 'deleteVoucher']);
    Route::post('/updateVoucher', [AdminVoucherController::class, 'updateVoucher']);
});


