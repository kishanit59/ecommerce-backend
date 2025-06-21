<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;


class Order extends Model
{
    protected $fillable = ['user_id', 'total_amount', 'status'];

    protected static function booted()
    {
        static::creating(function ($order) {
            if (auth()->check()) {
                $order->user_id = $order->user_id ?? auth()->id();
                $order->status = $order->status ?? 'pending';
                $order->total_amount = $order->total_amount ?? 0;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function calculateTotal()
    {
        // Debug: Log or return items to verify they're loaded
        $items = $this->items;

        // If no items, return 0
        if ($items->isEmpty()) {
            Log::info('No items found for order ID: ' . $this->id);
            return 0;
        }

        $total = $items->sum(function ($item) {
            // Debug: Log each item's contribution
            Log::info('OrderItem ID: ' . $item->id . ', Quantity: ' . $item->quantity . ', Price: ' . $item->price_at_purchase);
            return $item->quantity * $item->price_at_purchase;
        });

        Log::info('Calculated total for order ID: ' . $this->id . ' is: ' . $total);

        return $total;
    }
}