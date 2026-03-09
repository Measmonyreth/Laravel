<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = Storage::disk('public')->put('categories', $image);
        }

        $category = Category::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'image' => $path ?? null,
        ]);

        return response()->json(
            [
                'message' => 'Category created successful',
                'category' => $category,
            ], 200
        );

    }

    public function update(Request $request, $id)
    {
        $cate = Category::find($id);
        if (! $cate) {
            return response()->json([
                'message' => 'product not found',
            ], 404);
        }

        $cate->update($request->all());

        return response()->json(
            [
                'success' => true,
                'category' => $cate,
            ]
        );

    }

    public function index()
    {
        $categories = Category::all();
        // Add image URL to each category
        foreach ($categories as $category) {
            $category->image = $category->image ? asset('storage/'.$category->image) : null;
        }

        return response()->json([
            'success' => true,
            'data' => $categories,

        ]);
    }

    public function destroy($id)
    {
        $category = Category::find($id); // Find the category by ID if it exists
        if (! $category) {
            return response()->json([
                'message' => 'product not found',
            ], 404);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}
