<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        return view('cart.index', [
            'cartItems' => CartItem::with(['user', 'product'])->paginate(10)
        ]);
    }

    public function destroy(CartItem $cartItem)
    {
        $cartItem->delete();
        return redirect()->back()->with('success', 'Cart item deleted');
    }
}
