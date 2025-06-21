<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// routes/web.php
Route::get('/admin/get-user-cart/{user}', function (\App\Models\User $user) {
    $cartItems = $user->cartItems()->with('product')->get();
    
    return response()->json([
        'items' => $cartItems->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price
            ];
        })
    ]);
})->middleware(['web', 'auth']);
