<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shop;
use App\Models\User;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    //
    public function create(Request $request){
        $attrs = $request->validate([
            "name"=> "required|string",
            "name_ar"=> "required|string",
            "category_id"=> "required|int",
            "location"=> "required|string",
            "reg_no"=> "required|string",
            ]);

        // Check if shop name already exists
        $shop = DB::select("SELECT * FROM shops WHERE name=:name", [':name' => $attrs['name']]);
        if(!empty($shop))
        {
            return response([
                'status' => "0",
                'message' => "Shop name already exist"
            ], 200);
        }
        // Check if category exists
        $category = Category::find($attrs["category_id"]);
        if(!$category)
        {
            return response([
                "status"=> "0",
                "message" => "Category Not found."
            ]);
        }

        // Check If file exists
        $file_name = "";
        if(isset($_FILES['image']))
        {
            $file_name = $this->upload($request);
        }
        $images = [];
            if(isset($_FILES['galary_images']))
            {
                // print_r($_FILES['images']); exit;
                if ($request->hasFile('galary_images')) {
                    foreach ($request->file('galary_images') as $image) {
                        
                        $imageName = time() . '_' . $image->getClientOriginalName();
                        $image->move(public_path('images'), $imageName);
                        // You may also store the image information in the database if needed.
                        $images[] = $imageName;
                    }
        
                }
            }
        // Create New record
        $shop = Shop::create([
            "name"=> $attrs["name"],
            "name_ar"=> $attrs["name_ar"],
            "logo"=> $file_name,
            "category_id"=> $category->id,
            "location"=> $request->location,
            "reg_no"=> $attrs["reg_no"],
            "created_by"=> auth()->user()->id,
            "description"=> $request->description,
            'galary_images' => json_encode($images)
        ]);
        if(!empty($file_name))
        {
            $imageUrl = asset('images/'.$file_name);
            $shop['imageUrl'] = $imageUrl;
        } 
        if($shop)
        {
            return response([
                "status"=> "1",
                "shop" => $shop
            ]);
        } else {
            return response([
                "status"=> "0",
                "message" => "Something went wrong."
            ]);
        }
    }

    public function updateShop($id, Request $request){
        $attrs = $request->validate([
            "name"=> "required|string",
            // "name_ar"=> "required|string",
            "category_id"=> "required|int",
            "location"=> "required|string",
            "reg_no"=> "required|string",
            ]);

        // Check if shop name already exists
        $shop = DB::select("SELECT * FROM shops WHERE name=:name AND id != :id", [':name' => $attrs['name'], ':id' => $id]);
        if(!empty($shop))
        {
            return response([
                'status' => "0",
                'message' => "Shop name already exist"
            ], 200);
        }
        // Check if category exists
        $category = Category::find($attrs["category_id"]);
        if(!$category)
        {
            return response([
                "status"=> "0",
                "message" => "Category Not found."
            ]);
        }

        // Check If file exists
        $shop = DB::table("shops")->where("id","=", $id)->first();
        if(!empty($shop))
        {
            $file_name = "";
            if(isset($_FILES['image']))
            {
                $file_name = $this->upload($request);
            }
    
            $images = [];
            if(isset($_FILES['galary_images']))
            {
                // print_r($_FILES['images']); exit;
                if ($request->hasFile('galary_images')) {
                    foreach ($request->file('galary_images') as $image) {
                        
                        $imageName = time() . '_' . $image->getClientOriginalName();
                        $image->move(public_path('images'), $imageName);
                        // You may also store the image information in the database if needed.
                        $images[] = $imageName;
                    }
        
                }
            }
            // Create New record
            $shop = DB::table('shops')->where('id', '=', $id)->update([
                "name"=> $attrs["name"],
                "name_ar"=> $attrs["name_ar"],
                "logo"=> $file_name != "" ? $file_name : $shop->logo,
                "category_id"=> $category->id,
                "location"=> $request->location,
                "reg_no"=> $attrs["reg_no"],
                "description"=> isset($request->description) ? $request->description : $shop->description,
                "galary_images" => count($images) > 0 ? json_encode($images) : $shop->galary_images
            ]);
            
            if($shop)
            {
                return response([
                    "status"=> "1",
                    "shop" => json_decode(json_encode(DB::table('shops')->where('id','=', $id)->first()), true),
                    'image_base_url' => asset('images/')
                ]);
            } else {
                return response([
                    "status"=> "0",
                    "message" => "Something went wrong."
                ]);
            }

        } else {
            return response([
                "status"=> "0",
                "message" => "Something went wrong."
            ]);
        }
    }
    public function get(){
        $shop = Shop::where('created_by', auth()->user()->id)->get();
        if($shop)
        {
            return response([
                "status"=> "1",
                "shops" => $shop,
                'baseurl' => asset('images/')
            ]);
        } else {
            return response([
                "status"=> "0",
                "message" => "Shop not found."
            ]);
        }
    }

    public function getAllShops($cat_id){
        // echo auth()->user()->id; exit;
        $category = DB::select("SELECT * FROM categories WHERE id=:id AND status=1", [":id"=> $cat_id]);
        if(count($category) > 0)
        {
            $shops = DB::select("SELECT * FROM shops WHERE status=1 AND FIND_IN_SET(:cat_id, category_id) > 0", [":cat_id" => $cat_id]);
            if(count($shops) > 0){
                // $shop['image_base_url'] = asset('images/');
                return response([
                    "status"=> "1",
                    "shops"=> json_decode(json_encode($shops), true),
                    "catgeory"=> json_decode(json_encode($category[0]), true),
                    "image_base_url" => asset('images/')
                ]);
            } else {
                return response([
                    "status"=> "0",
                    "message"=> "Shops not found"
                ]);
            }

        } else {
            return response([
                "status"=> "0",
                "message"=> "Category not found"
            ], 200);
        }
    }
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $image = $request->file('image');
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);

        return $imageName;
    }
    public function delete($id)
    {
        $shop = Shop::find( $id );
        $products = DB::select('SELECT id FROM products WHERE shop_id=:s_id', [':s_id' => $id]);
        // print_r($shop); exit;
        if($shop){
            if(count($products) == 0 && $shop->delete())
            {
                return response([
                    'status'=> '1',
                    'message' => "shop Delete successfully"
                ], 200);
            }else if($shop) {
                return response([
                    "status"=> "0",
                    "message"=> "You cannot delete this shop, This is use in products."
                ],200);
            }
        } else {
            return response([
                "status"=> "0",
                "message"=> "Shop not found"
            ],200);
        }
    }

    public function shops()
    {
        $shops = DB::table('shops')->where("status",1)->get();
        // print_r(json_decode(json_encode($shops), true));
        if(count($shops) > 0)
        {
            return response([
                "status"=> "1",
                "shops"=> json_decode(json_encode($shops), true),
            ],200);
        } else {
            return response([
                "status"=> "0",
                "message"=> "shops not found"
            ],200);
        }
    }

    public function manualOrder(Request $request)
    {
        $attrs = $request->validate([
            "description"=> "required",
            "shop_id" => "required|int"
        ]);

        $shop = Shop::find($attrs['shop_id']);

        $order = Order::create([
            "user_id"=> isset(auth()->user()->id) ? auth()->user()->id : 0,
            "seller_id" => $shop->created_by,
            "manual_order" => 1,
            "description" => $request->description,
        ]);

        if($order){
            $notification = new Notification();
                $notification->user_id = $order->user_id; // Assuming the user is authenticated
                $notification->message = 'Your manual order placed successfully';
                $notification->page = 'menual_orders';
                $notification->save();

                $notification->user_id = $shop->created_by; // Assuming the user is authenticated
                $notification->message = 'Your Shop have new manual order';
                $notification->page = 'menual_orders';
                $notification->save();

            $userData = User::find($shop->created_by);
            $data = [];
            $data['title'] = 'New Menual Order';
            $data['body'] = 'Your Shop have new manual order';
            $data['device_token'] = $userData->device_token;
            return response([
                "status"=> "1",
                "order" => json_decode(json_encode($order), true),
                "push_notification_status" => User::sendNotification($data),
            ]);
        } else {
            return response([
                "status"=> "0",
                "message" => "Something Went Wrong",
            ]);
        }
    }
    
    function notificationList()
    {
        $user = auth()->user();

        $list = Notification::where('is_read', 0)->where('user_id', $user->id)->get();
        return response()->json(['data' => $list]);
    }
    function readNotification(Request $req)
    {
        $req->validate([
            'id' => 'required | int',
            'is_all_read' => 'required'
        ]);

        if($req->is_all_read == 1)
        {
            $notify = Notification::where('user_id', auth()->user()->id)->update(['is_read', 1]);
            return response()->json(['msg' => 'All notification read successfully']);
        } else {
            $notify = Notification::where('user_id', auth()->user()->id)->where('id', $req->id)->update(['is_read', 1]);
            return response()->json(['msg' => 'Notification read successfully']);
        }
    }

}
