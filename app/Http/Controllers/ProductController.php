<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    protected $fcmService;

    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function store(Request $request)
    {

        $imagePath = null;
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            // 'is_featured' => 'boolean',
            'category_id' => 'required|exists:categories,id',
        ]);

        // dd($request->file('image'));
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = Storage::disk('public')->put('products', $image);
        }

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $imagePath, // store relative path in DB
            'is_featured' => $request->is_featured,
            'category_id' => $request->category_id,
        ]);

        //    $this->fcmService->sendToTopic(
        //     "all",
        //     "New Product Added: " . $request->name,
        //     "A new product has been added to the store: " . $request->name,
        //     [
        //       "title"=> "New Product Added",
        //         "body"=> "A new product has been added to the store: " . $request->name,
        //     ]
        // );
        return response()->json([
            'message' => 'Product created successfully',
            'product' => [
                ...$product->toArray(),
                'image' => $imagePath ? asset('storage/'.$imagePath) : null, // ← full URL
            ],
        ], 200);
    }

    public function index()
    {
        $categories = Category::with([
            'products' => function ($query) {
                $query->select('id', 'name', 'description', 'price', 'image', 'is_featured', 'category_id');
            },
        ])->latest()->get(['id', 'name', 'image']);

        // group products by category
        $categories = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'image' => $category->image ? asset('storage/'.$category->image) : null,
                'products' => $category->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'price' => $product->price,
                        'image' => asset($product->image) ? asset('storage/'.$product->image) : null,
                        'is_featured' => $product->is_featured,
                    ];
                }),
            ];
        });
        // get featured products
        $featuredProducts = Product::where('is_featured', 1)->latest()->limit(5)->get(['id', 'name', 'description', 'price', 'image']);

        $featuredProducts = $featuredProducts->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'image' => asset($product->image) ? asset('storage/'.$product->image) : null,
            ];
        });

        return response()->json([
            'success' => true,
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
        ]);
    }

    public function getProductByCate($cateId)
    {
        $category = Category::find($cateId);
        if (! $category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }
        $products = $category->products()->paginate(10, ['id', 'name', 'description', 'price', 'image']);

        return response()->json($products);

        // $products = $products->map(function($product){
        //     return [
        //         'id'=> $product->id,
        //         'name'=> $product->name,
        //         'description'=> $product->description,
        //         'price'=> $product->price,
        //         'image'=> asset($product->image) ? asset('storage/'.$product->image) : null
        //     ];
        // });
        return response()->json($products);
    }

    public function search(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'min_price' => 'numeric',
            'max_price' => 'numeric',
        ]);
        $name = $request->name;
        $min_price = $request->min_price;
        $max_price = $request->max_price;
        $products = Product::where('name', 'like', '%'.$name.'%')
            ->when($min_price, function ($query) use ($min_price) {
                return $query->where('price', '>=', $min_price);
            })
            ->when($max_price, function ($query) use ($max_price) {
                return $query->where('price', '<=', $max_price);
            })
            ->get(['id', 'name', 'description', 'price', 'image']);

        $products = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'image' => asset($product->image) ? asset('storage/'.$product->image) : null,
            ];
        });

        return response()->json($products);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }
        $product->update($request->all());

        if ($request->has('price')) {
            $cartItems = CartItem::where('product_id', $id)->get();

            foreach ($cartItems as $item) {
                $item->update(['price' => $request->price]);

                // Recalculate total for each affected cart
                $newTotal = CartItem::where('cart_id', $item->cart_id)
                    ->get()
                    ->sum(fn ($i) => $i->price * $i->quantity);

                Cart::where('id', $item->cart_id)
                    ->update(['total' => $newTotal]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'product' => $product,
        ], 200);
    }
}
