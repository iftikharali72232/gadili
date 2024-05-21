<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Wallet;
use App\Models\WalletHistory;
use Carbon\Carbon;

class PaymentController extends Controller
{
    //
    public function create(Request $request)
    {
        $attrs = $request->validate([
            "name"=> "required|string|unique:payment_methods",
            "name_ar"=> "required|string",
            "slug"=> "required|string|unique:payment_methods",
        ]);
       
        $paymetMethod = PaymentMethod::create([
            "name"=> $attrs["name"],
            "name_ar"=> $attrs["name_ar"],
            "status"=> 1,
            "public_key"=> $request->public_key,
            "secret_key"=> $request->secret_key,
            "created_by"=> auth()->user()->id,
            "slug"=> $request->slug,
        ]);

        if($paymetMethod)
        {
            return response([
                "status" => "1",
                "payment_method" => json_decode(json_encode($paymetMethod), true),
            ]);
        } else {
            return response([
                "status"=> "0",
                "message" => "Something went wrong"
            ]);
        }
    }

    public function list(){
        $list = DB::table("payment_methods")->where("status", 1)->get();

        if($list)
        {
            return response([
                "status"=> "1",
                "list"=> json_decode(json_encode($list), true)
            ]);
        } else {
            return response([
                "status"=> "0",
                "message"=> "Payment list not found"
            ]);
        }
    }

    public function sellerAccountHistory()
    {
        $user = auth()->user();
        $balance = Wallet::select('amount','id')->where('user_id', $user->id)->first();
        // print_r($balance->amount);

        $total_earning = Order::where('seller_id', $user->id)->where('order_status', 1)->sum('total');

        $total_withdraw = WalletHistory::where('wallet_id', $balance->id)->where('is_expanse', 1)->sum('amount');
        // echo $total_withdraw;

        
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // Query the orders between these dates
        $orders = Order::with('user')->where('created_at', '>=', $thirtyDaysAgo)->where('seller_id', $user->id)->where('order_status', 1)->get();
        // print_r($orders);

        return response()->json([
            'balance' => $balance->amount,
            'total_earnings' => $total_earning,
            'total_withdraw' => $total_withdraw,
            'last_thirty_days_orders' => $orders
        ]);
    }
}
