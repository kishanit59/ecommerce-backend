<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        return view('orders.index', [
            'orders' => Order::with(['user', 'items.product'])->paginate(10)
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $order = DB::transaction(function () use ($validated) {
            $order = Order::create([
                'user_id' => $validated['user_id'],
                'total_amount' => 0,
                'status' => 'pending'
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                // Debug: Log product price
                Log::info('Product ID: ' . $item['product_id'] . ', Price: ' . $product->price);

                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price_at_purchase' => $product->price
                ]);
            }

            // Update total_amount
            $total = $order->calculateTotal();
            Log::info('Updating order ID: ' . $order->id . ' with total: ' . $total);
            $order->update(['total_amount' => $total]);

            return $order;
        });

        return redirect()->route('orders.index')->with('success', 'Order created');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $order->update($validated);
        return redirect()->back()->with('success', 'Order status updated');
    }
}