<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth; 
use App\Models\Customer; 
use App\Models\Produk; 
use App\Models\Order; 
use App\Models\OrderItem; 

class OrderController extends Controller
{
    //
    public function addToCart($id) 
    { 
       
        $customer = Customer::where('user_id', Auth::id())->first(); 
        $produk = Produk::findOrFail($id); 
 
        $order = Order::firstOrCreate( 
            ['customer_id' => $customer->id, 'status' => 'pending'], 
            ['total_harga' => 0] 
        ); 
 
        $orderItem = OrderItem::firstOrCreate( 
            ['order_id' => $order->id, 'produk_id' => $produk->id], 
            ['quantity' => 1, 'harga' => $produk->harga] 
        ); 
 
        if (!$orderItem->wasRecentlyCreated) { 
            $orderItem->quantity++; 
            $orderItem->save(); 
        } 
 
        $order->total_harga += $produk->harga; 
        $order->save(); 
 
        return redirect()->route('order.cart')->with('success', 'Produk berhasil 
ditambahkan ke keranjang');
    } 
 
    public function viewCart() 
    { 
        $customer = Customer::where('user_id', Auth::id())->first(); 
        $order = Order::where('customer_id', $customer->id)->where('status', 'pending', 
'paid')->first(); 
        if ($order) { 
            $order->load('orderItems.produk'); 
        } 
        return view('v_order.cart', compact('order')); 
    }
    public function updateCart($item_id)
{
    $orderItem = OrderItem::findOrFail($item_id);

    // Pastikan item milik customer yang login
    $customer = Customer::where('user_id', Auth::id())->first();
    if ($orderItem->order->customer_id !== $customer->id) {
        return redirect()->back()->with('error', 'Akses ditolak');
    }

    $quantity = request('quantity');

    if ($quantity < 1) {
        return redirect()->back()->with('error', 'Jumlah tidak valid');
    }

    $orderItem->quantity = $quantity;
    $orderItem->save();

    // Update total harga di order
    $order = $orderItem->order;
    $order->total_harga = $order->orderItems->sum(function ($item) {
        return $item->harga * $item->quantity;
    });
    $order->save();

    return redirect()->back()->with('success', 'Keranjang diperbarui');
}

}
