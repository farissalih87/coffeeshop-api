<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // ── Public: Customer places order ─────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:100',
            'location'      => 'required|in:salon,reception',
            'items'         => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'notes'         => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // Calculate total from DB prices (never trust client prices)
            $total = 0;
            $itemsData = [];
            foreach ($request->items as $item) {
                $menuItem = MenuItem::findOrFail($item['menu_item_id']);
                $subtotal = $menuItem->price * $item['quantity'];
                $total += $subtotal;
                $itemsData[] = [
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity'     => $item['quantity'],
                    'price'        => $menuItem->price,
                    'subtotal'     => $subtotal,
                ];
            }

            $order = Order::create([
                'customer_name' => $request->customer_name,
                'location'      => $request->location,
                'notes'         => $request->notes,
                'total'         => $total,
                'status'        => 'pending',
                'lang'          => $request->lang ?? 'en',
            ]);

            foreach ($itemsData as $item) {
                $order->items()->create($item);
            }

            DB::commit();

            return response()->json([
                'message' => 'Order placed successfully',
                'order'   => $order->load('items.menuItem'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to place order'], 500);
        }
    }

    // ── Staff: View orders ────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Order::with('items.menuItem')->latest();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date) {
            $query->whereDate('created_at', $request->date);
        }

        $orders = $query->limit(100)->get();

        return response()->json(['orders' => $orders]);
    }

    public function show(Order $order)
    {
        return response()->json(['order' => $order->load('items.menuItem')]);
    }

    // ── Staff: Update order status ────────────────────────────────────────────
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,preparing,ready,delivered,cancelled',
        ]);

        $validTransitions = [
            'pending'   => ['preparing', 'cancelled'],
            'preparing' => ['ready', 'cancelled'],
            'ready'     => ['delivered'],
            'delivered' => [],
            'cancelled' => [],
        ];

        $currentStatus = $order->status;
        $newStatus = $request->status;

        if (!in_array($newStatus, $validTransitions[$currentStatus] ?? [])) {
            return response()->json([
                'message' => "Cannot transition from {$currentStatus} to {$newStatus}"
            ], 422);
        }

        $order->update(['status' => $newStatus]);

        return response()->json(['order' => $order->fresh()->load('items.menuItem')]);
    }
}
