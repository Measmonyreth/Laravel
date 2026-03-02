<?php

namespace App\Http\Controllers;

use App\Models\Save;
use App\Models\User;
use Illuminate\Http\Request;

class SaveController extends Controller
{
    //
    public function save(Request $request)
    {
        $user = $request->user();
        if(!$user){
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $existingSave = Save::where('user_id', $user->id)
            ->where('product_id', $request->input('product_id'))
            ->where('status', 'inactive')
            ->first();
        if ($existingSave) {
            $existingSave->update(['status' => 'active']);
            return response()->json(['message' => 'Product saved successfully', 'save' => $existingSave], 200);
        }

        // validate id of product already exists in saves table with status active for the user
        $existingSave = Save::where('user_id', $user->id)
            ->where('product_id', $request->input('product_id'))
            ->where('status', 'active')
            ->first();

        if ($existingSave) {
            return response()->json(['message' => 'Product already saved'], 400);
        }

        // Create a new save record with the validated data
        $save = Save::create([
            'user_id' => $user->id,
            'product_id' => $request->input('product_id'),
            'status' => 'active',
        ]);

        // Return a response indicating success
        return response()->json(['message' => 'Product saved successfully', 'save' => $save], 201);
    }

    public function unsave(Request $request)
    {
        $user = $request->user();

        // Find the save record for the user and product
        $save = Save::where('user_id', $user->id)
            ->where('product_id', $request->input('product_id'))
            ->first();

        if ($save) {
            // Update the status to 'inactive' or delete the record
            $save->update(['status' => 'inactive']);
            // Or you can choose to delete the record instead
            // $save->delete();
        }

        // Return a response indicating success
        return response()->json(['message' => 'Product unsaved successfully'], 200);
    }

    public function getSavedProducts(Request $request)
    {
        $user = $request->user();

        // Get the saved products for the user
        $savedProducts = $user->saves()->where('status', 'active')->with('product')->get();
        // count the number of saved products
        $savedProductsCount = $savedProducts->count();

        // Return the saved products
        return response()->json(['saved_products' => $savedProducts, 'count' => $savedProductsCount], 200);
    }

}
