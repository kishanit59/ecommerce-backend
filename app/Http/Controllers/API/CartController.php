<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cartItems = $request->user()->cartItems()->with('product')->get();
        return CartResource::collection($cartItems);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'sometimes|integer|min:1'
        ]);

        $cartItem = $request->user()->cartItems()
            ->updateOrCreate(
                ['product_id' => $validated['product_id']],
                ['quantity' => $validated['quantity'] ?? 1]
            );

        return new CartResource($cartItem->load('product'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, CartItem $cartItem)
    {
        abort_if($cartItem->user_id !== $request->user()->id, 403);
        $cartItem->delete();
        return response()->noContent();
    }
}
