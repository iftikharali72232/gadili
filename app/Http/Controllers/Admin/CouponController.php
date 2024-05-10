<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    //
    public function index()
    {
        $coupons = Coupon::all();
        return response()->json(['status' => 1, 'data' => $coupons]);
    }
    public function show(Coupon $coupon)
    {
        return response()->json(['status' => 1, 'data' => $coupon]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:coupons,code',
            'discount' => 'required|numeric',
            'expires_at' => 'nullable|date',
        ]);

        $coupon = Coupon::create($request->all());
        return response()->json(['status' => 1, 'data' => $coupon]);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'code' => 'required|unique:coupons,code,' . $coupon->id,
            'discount' => 'required|numeric',
            'expires_at' => 'nullable|date',
        ]);

        $coupon->update($request->all());
        return response()->json(['status' => 1, 'data' => $coupon]);
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return response()->json(['message' => 'Coupon deleted successfully'], 200);
    }
}
