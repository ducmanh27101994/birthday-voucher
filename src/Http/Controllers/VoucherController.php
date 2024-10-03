<?php

namespace Fmcpay\BirthdayVoucher\Http\Controllers;

use Fmcpay\BirthdayVoucher\Console\GenerateBirthdayVouchersCommand;
use Illuminate\Http\Request;
use Fmcpay\BirthdayVoucher\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VoucherController
{
    public function indexVoucherUser(Request $request)
    {
        $user_id = $request->input('id');
        if (!$user_id) {
            return response()->json([
                'message' => 'Chưa có id người dùng',
                'status' => 400,
            ]);
        }

        $vouchers = Voucher::where('user_id', $user_id)
            ->where('code', GenerateBirthdayVouchersCommand::CODE)
            ->get();

        return response()->json([
            'message' => 'Success',
            'status' => 200,
            'data' => $vouchers ?? []
        ]);
    }

    public function useVoucher(Request $request)
    {
        $voucher_id = $request->input('id');
        if (!$voucher_id) {
            return response()->json([
                'message' => 'Chưa có id voucher',
                'status' => 400,
            ]);
        }

        $voucher = Voucher::where('id', $voucher_id)
            ->where('code', GenerateBirthdayVouchersCommand::CODE)
            ->with('user')
            ->first();
        if ($voucher->is_used || $voucher->expires_at < now()) {
            return response()->json([
                'message' => 'Voucher đã được sử dụng hoặc hết hạn',
                'status' => 400,
            ]);
        }
        if (!$voucher->user || !$voucher->user->wallet) {
            return response()->json([
                'message' => 'Người dùng chưa tạo ví',
                'status' => 400,
            ]);
        }

        $this->useVoucherTransaction($voucher);

        return response()->json([
            'message' => 'Voucher used successfully',
            'status' => 200,
        ]);
    }

    private function useVoucherTransaction($voucher)
    {
        DB::transaction(function () use ($voucher) {
            $wallet = $voucher->user->wallet;
            $previousBalance = $wallet->balance;
            $wallet->currency = GenerateBirthdayVouchersCommand::CURRENCY;
            $wallet->balance += $voucher->amount;
            $wallet->save();

            Log::channel('use_voucher_birthday')->info("Cộng tiền vào ví: Người dùng: {$voucher->user->name}, Giá trị trước: {$previousBalance}, Giá trị sau: {$wallet->balance}");

            $voucher->is_used = true;
            $voucher->save();
        });
    }

    public function listAllVoucher()
    {
        $vouchers = Voucher::all();

        return response()->json([
            'message' => 'Success',
            'status' => 200,
            'data' => $vouchers ?? []
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
        $voucher->is_used = $input['is_used'] ?? 0;
        $voucher->save();

        return response()->json([
            'message' => 'Voucher đã được cập nhật thành công.',
            'status' => 200,
        ]);

    }



}
