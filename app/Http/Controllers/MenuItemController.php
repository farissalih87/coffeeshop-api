<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuItemController extends Controller
{
    public function index(Request $request)
    {
        $items = MenuItem::with('category')
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->get()
            ->map(fn($item) => $this->formatItem($item));

        return response()->json(['items' => $items]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image'       => 'nullable|image|max:5120', // 5MB max
        ]);

        $data = $request->only(['name', 'name_ar', 'description', 'description_ar', 'price', 'category_id']);
        $data['available'] = $request->boolean('available', true);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('menu-items', 'public');
        }

        $item = MenuItem::create($data);

        return response()->json(['item' => $this->formatItem($item->load('category'))], 201);
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $request->validate([
            'name'  => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'image' => 'nullable|image|max:5120',
        ]);

        $data = $request->only(['name', 'name_ar', 'description', 'description_ar', 'price', 'category_id']);

        if ($request->has('available')) {
            $data['available'] = filter_var($request->available, FILTER_VALIDATE_BOOLEAN);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($menuItem->image) {
                Storage::disk('public')->delete($menuItem->image);
            }
            $data['image'] = $request->file('image')->store('menu-items', 'public');
        }

        // Handle image removal
        if ($request->remove_image && $menuItem->image) {
            Storage::disk('public')->delete($menuItem->image);
            $data['image'] = null;
        }

        $menuItem->update($data);

        return response()->json(['item' => $this->formatItem($menuItem->fresh('category'))]);
    }

    public function destroy(MenuItem $menuItem)
    {
        if ($menuItem->image) {
            Storage::disk('public')->delete($menuItem->image);
        }
        $menuItem->delete();
        return response()->json(['message' => 'Item deleted']);
    }

    private function formatItem(MenuItem $item): array
    {
        return [
            'id'             => $item->id,
            'name'           => $item->name,
            'name_ar'        => $item->name_ar,
            'description'    => $item->description,
            'description_ar' => $item->description_ar,
            'price'          => $item->price,
            'available'      => $item->available,
            'category_id'    => $item->category_id,
            'category'       => $item->category,
            // Full URL for the image
            'image'          => $item->image
                                  ? asset('storage/' . $item->image)
                                  : null,
        ];
    }
}
