<?php

namespace Fmcpay\BirthdayVoucher\Http\Controllers;

use Fmcpay\BirthdayVoucher\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminVoucherController
{
    public function listAllVoucher(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $vouchers = Voucher::paginate($perPage);

        return response()->json([
            'message' => 'Success',
            'status' => 200,
            'data' => $vouchers ?? [],
            'paginate' => [
                'current_page' => $vouchers->currentPage(),
                'total' => $vouchers->total(),
                'per_page' => $vouchers->perPage(),
                'last_page' => $vouchers->lastPage(),
            ],
        ]);
    }

    public function createVoucher(Request $request)
    {
        $input = $request->all();
        $validate = Validator::make($input, [
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric',
            'expires_at' => 'required|date',
            'code' => 'string',
            'currency' => 'required|string',
        ], [
            'user_id.required' => 'ID người dùng là bắt buộc.',
            'user_id.exists' => 'Người dùng không tồn tại.',
            'amount.required' => 'Số tiền là bắt buộc.',
            'amount.numeric' => 'Số tiền phải là số.',
            'expires_at.required' => 'Ngày hết hạn là bắt buộc.',
            'expires_at.date' => 'Ngày hết hạn không hợp lệ.',
            'code.string' => 'Mã voucher phải là chuỗi ký tự.',
            'currency.required' => 'Loại tiền tệ là bắt buộc.',
            'currency.string' => 'Loại tiền tệ phải là chuỗi.',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors(),
                'status' => 400,

            ]);
        }

        Voucher::create([
            'id' => Str::uuid(),
            'user_id' => $input['user_id'],
            'amount' => $input['amount'],
            'expires_at' => $input['expires_at'],
            'code' => $input['code'],
            'currency' => $input['currency'],
        ]);

        return response()->json([
            'message' => 'Voucher đã được tạo thành công.',
            'status' => 200,
        ]);
    }

    public function deleteVoucher(Request $request)
    {
        $voucher_id = $request->input('id');
        if (!$voucher_id) {
            return response()->json([
                'message' => 'Chưa có id voucher',
                'status' => 400,
            ]);
        }

        $voucher = Voucher::findOrFail($voucher_id);
        $voucher->delete();

        return response()->json([
            'message' => 'Voucher đã được xóa',
            'status' => 200,
        ]);
    }

    public function updateVoucher(Request $request)
    {
        $input = $request->all();
        $validate = Validator::make($input, [
            'id' => 'required',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric',
            'expires_at' => 'required|date',
            'code' => 'string',
            'currency' => 'required|string',
            'is_used' => 'required',
        ], [
            'id.required' => 'ID không được để trống',
            'user_id.required' => 'ID người dùng là bắt buộc.',
            'user_id.exists' => 'Người dùng không tồn tại.',
            'amount.required' => 'Số tiền là bắt buộc.',
            'amount.numeric' => 'Số tiền phải là số.',
            'expires_at.required' => 'Ngày hết hạn là bắt buộc.',
            'expires_at.date' => 'Ngày hết hạn không hợp lệ.',
            'code.string' => 'Mã voucher phải là chuỗi ký tự.',
            'currency.required' => 'Loại tiền tệ là bắt buộc.',
            'currency.string' => 'Loại tiền tệ phải là chuỗi.',
            'is_used.required' => 'Trạng thái voucher không được để trống',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors(),
                'status' => 400,
            ]);
        }

        $voucher = Voucher::find($input['id']);
        if (!$voucher) {
            return response()->json([
                'message' => 'Voucher không tồn tại.',
                'status' => 400,
            ]);
        }

        $voucher->user_id = $input['user_id'];
        $voucher->amount = $input['amount'];
        $voucher->expires_at = $input['expires_at'];
        $voucher->code = $input['code'];
        $voucher->currency = $input['currency'];
        $voucher->is_used = $input['is_used'];
        $voucher->save();

        return response()->json([
            'message' => 'Voucher đã được cập nhật thành công.',
            'status' => 200,
        ]);

    }
}
