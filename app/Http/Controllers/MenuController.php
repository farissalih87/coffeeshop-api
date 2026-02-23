<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    // ── Public ────────────────────────────────────────────────────────────────

    public function categories()
    {
        $categories = Category::orderBy('sort_order')->get();
        return response()->json(['categories' => $categories]);
    }

    public function items(Request $request)
    {
        $query = MenuItem::with('category');

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $items = $query->where('available', true)->orderBy('sort_order')->get();

        return response()->json(['items' => $items]);
    }

    // ── Admin: Categories ─────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'name_ar' => 'nullable|string|max:100',
            'icon'    => 'nullable|string|max:10',
        ]);

        $category = Category::create($data);
        return response()->json(['category' => $category], 201);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'    => 'sometimes|string|max:100',
            'name_ar' => 'nullable|string|max:100',
            'icon'    => 'nullable|string|max:10',
        ]);

        $category->update($data);
        return response()->json(['category' => $category]);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ── Admin: Items ──────────────────────────────────────────────────────────

    public function storeItem(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'name_ar'         => 'nullable|string|max:100',
            'description'     => 'nullable|string',
            'description_ar'  => 'nullable|string',
            'price'           => 'required|numeric|min:0',
            'category_id'     => 'required|exists:categories,id',
            'available'       => 'boolean',
            'image'           => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('menu', 'public');
        }

        $item = MenuItem::create($data);
        return response()->json(['item' => $item->load('category')], 201);
    }

    public function updateItem(Request $request, MenuItem $item)
    {
        $data = $request->validate([
            'name'            => 'sometimes|string|max:100',
            'name_ar'         => 'nullable|string|max:100',
            'description'     => 'nullable|string',
            'description_ar'  => 'nullable|string',
            'price'           => 'sometimes|numeric|min:0',
            'category_id'     => 'sometimes|exists:categories,id',
            'available'       => 'boolean',
            'image'           => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($item->image) Storage::disk('public')->delete($item->image);
            $data['image'] = $request->file('image')->store('menu', 'public');
        }

        $item->update($data);
        return response()->json(['item' => $item->load('category')]);
    }

    public function destroyItem(MenuItem $item)
    {
        if ($item->image) Storage::disk('public')->delete($item->image);
        $item->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
