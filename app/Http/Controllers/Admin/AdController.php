<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Product;
use Illuminate\Http\Request;

class AdController extends Controller
{
    public function index()
    {
        $ads = Ad::all();
        return response()->json($ads);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'discount' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'images' => 'required'
        ]);
        $product = Product::find($data['product_id']);
        if($product)
        {
            $images = [];
            if(isset($_FILES['images']))
            {
                // print_r($_FILES['images']); exit;
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $image) {
                        
                        $imageName = time() . '_' . $image->getClientOriginalName();
                        $image->move(public_path('images'), $imageName);
                        // You may also store the image information in the database if needed.
                        $images[] = $imageName;
                    }
        
                }
            }
            $data['images'] = json_encode($images);
            $ad = Ad::create($data);
            return response()->json($ad, 201);

        } else {
            return response()->json(['msg' => "Product not found"]);
        }
    }

    public function show(Ad $ad)
    {
        return response()->json($ad);
    }

    public function update(Request $request, Ad $ad)
    {
        $data = $request->validate([
            'product_id' => 'integer|exists:products,id',
            'discount' => 'numeric',
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);
        print_r($request->hasFile('images')); exit;
        $ad = Ad::find($ad->id);
        $images = json_decode($ad->images, true);
            if(isset($_FILES['images']))
            {
                // print_r($_FILES['images']); exit;
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $image) {
                        
                        $imageName = time() . '_' . $image->getClientOriginalName();
                        $image->move(public_path('images'), $imageName);
                        // You may also store the image information in the database if needed.
                        $images[] = $imageName;
                    }
        
                }
            }
            $data['images'] = json_encode($images);
        $ad->update($data);
        return response()->json($ad);
    }

    public function destroy(Ad $ad)
    {
        $ad->delete();
        return response()->json(['message' => 'Ad deleted successfully'], 200);
    }
}
