<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function stats()
    {
        $today = Carbon::today();

        $stats = [
            'total_orders'     => Order::count(),
            'today_orders'     => Order::whereDate('created_at', $today)->count(),
            'total_revenue'    => Order::where('status', 'delivered')->sum('total'),
            'today_revenue'    => Order::whereDate('created_at', $today)->where('status', 'delivered')->sum('total'),
            'pending_orders'   => Order::where('status', 'pending')->count(),
            'active_staff'     => User::where('role', 'staff')->count(),
            'status_breakdown' => [
                'pending'   => Order::where('status', 'pending')->count(),
                'preparing' => Order::where('status', 'preparing')->count(),
                'ready'     => Order::where('status', 'ready')->count(),
                'delivered' => Order::where('status', 'delivered')->count(),
                'cancelled' => Order::where('status', 'cancelled')->count(),
            ],
            'recent_orders' => Order::with('items.menuItem')
                                    ->latest()
                                    ->limit(5)
                                    ->get(),
        ];

        return response()->json($stats);
    }

    public function reports(Request $request)
    {
        $period = $request->get('period', 'month');

        $startDate = match($period) {
            'week'  => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'year'  => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };

        $orders = Order::with('items.menuItem')
                       ->where('created_at', '>=', $startDate)
                       ->get();

        $totalOrders   = $orders->count();
        $totalRevenue  = $orders->where('status', 'delivered')->sum('total');
        $avgOrder      = $totalOrders > 0 ? round($totalRevenue / max($orders->where('status','delivered')->count(), 1), 1) : 0;

        // Top items
        $itemCounts = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $name = $item->menuItem?->name ?? 'Unknown';
                if (!isset($itemCounts[$name])) {
                    $itemCounts[$name] = ['name' => $name, 'orders' => 0, 'revenue' => 0];
                }
                $itemCounts[$name]['orders']++;
                $itemCounts[$name]['revenue'] += $item->subtotal;
            }
        }
        usort($itemCounts, fn($a,$b) => $b['orders'] - $a['orders']);
        $topItems = array_slice(array_values($itemCounts), 0, 10);

        // By location
        $salonOrders     = $orders->where('location', 'salon');
        $receptionOrders = $orders->where('location', 'reception');

        $byLocation = [
            ['location' => "Men's Salon",           'orders' => $salonOrders->count(),     'revenue' => $salonOrders->sum('total'),     'percent' => $totalOrders > 0 ? round($salonOrders->count() / $totalOrders * 100) : 0],
            ['location' => 'Car Care Reception',     'orders' => $receptionOrders->count(), 'revenue' => $receptionOrders->sum('total'), 'percent' => $totalOrders > 0 ? round($receptionOrders->count() / $totalOrders * 100) : 0],
        ];

        // Daily breakdown (last 7 days)
        $daily = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayOrders = Order::whereDate('created_at', $date)->get();
            $daily[] = [
                'date'    => $date->format('D'),
                'orders'  => $dayOrders->count(),
                'revenue' => $dayOrders->sum('total'),
            ];
        }

        return response()->json(compact('totalOrders','totalRevenue','avgOrder','topItems','byLocation','daily'));
    }

    // ── User Management ───────────────────────────────────────────────────────

    public function users()
    {
        $users = User::select('id','name','email','role','created_at')->get();
        return response()->json(['users' => $users]);
    }

    public function createUser(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role'     => 'required|in:staff,admin',
        ]);
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        return response()->json(['user' => $user->only(['id','name','email','role'])], 201);
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'sometimes|string|max:100',
            'email'    => 'sometimes|email|unique:users,email,'.$user->id,
            'password' => 'nullable|min:6',
            'role'     => 'sometimes|in:staff,admin',
        ]);
        if (empty($data['password'])) unset($data['password']);
        else $data['password'] = Hash::make($data['password']);
        $user->update($data);
        return response()->json(['user' => $user->only(['id','name','email','role'])]);
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Cannot delete yourself'], 403);
        }
        $user->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
