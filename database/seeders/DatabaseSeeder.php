<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Users ─────────────────────────────────────────────────────────────
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@coffeeshop.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        User::create([
            'name'     => 'Staff',
            'email'    => 'staff@coffeeshop.com',
            'password' => Hash::make('password'),
            'role'     => 'staff',
        ]);

        // ── Categories ────────────────────────────────────────────────────────
        $categories = [
            ['name' => 'Hot Coffee',  'name_ar' => 'قهوة ساخنة', 'icon' => '☕', 'sort_order' => 1],
            ['name' => 'Cold Coffee', 'name_ar' => 'قهوة باردة', 'icon' => '🧊', 'sort_order' => 2],
            ['name' => 'Tea',         'name_ar' => 'شاي',         'icon' => '🍵', 'sort_order' => 3],
            ['name' => 'Pastries',    'name_ar' => 'معجنات',      'icon' => '🥐', 'sort_order' => 4],
            ['name' => 'Juices',      'name_ar' => 'عصائر',       'icon' => '🍊', 'sort_order' => 5],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // ── Menu Items ────────────────────────────────────────────────────────
        $items = [
            // Hot Coffee
            ['category_id'=>1,'name'=>'Espresso',     'name_ar'=>'إسبريسو',      'description'=>'Rich intense single shot',            'description_ar'=>'شوت واحد غني وقوي',            'price'=>12],
            ['category_id'=>1,'name'=>'Double Espresso','name_ar'=>'دبل إسبريسو', 'description'=>'Double shot of pure espresso',         'description_ar'=>'شوتين من الإسبريسو',           'price'=>15],
            ['category_id'=>1,'name'=>'Americano',    'name_ar'=>'أمريكانو',     'description'=>'Espresso diluted with hot water',       'description_ar'=>'إسبريسو مع ماء ساخن',          'price'=>15],
            ['category_id'=>1,'name'=>'Cappuccino',   'name_ar'=>'كابتشينو',     'description'=>'Espresso with steamed milk and foam',   'description_ar'=>'إسبريسو مع رغوة الحليب الناعمة','price'=>18],
            ['category_id'=>1,'name'=>'Latte',        'name_ar'=>'لاتيه',        'description'=>'Smooth espresso with steamed milk',     'description_ar'=>'إسبريسو ناعم مع الحليب',       'price'=>20],
            ['category_id'=>1,'name'=>'Flat White',   'name_ar'=>'فلات وايت',    'description'=>'Double ristretto with velvety milk',    'description_ar'=>'ريستريتو مزدوج مع حليب ناعم',  'price'=>20],
            ['category_id'=>1,'name'=>'Macchiato',    'name_ar'=>'ماكياتو',      'description'=>'Espresso with a touch of foam',         'description_ar'=>'إسبريسو مع رغوة خفيفة',        'price'=>16],
            // Cold Coffee
            ['category_id'=>2,'name'=>'Iced Latte',   'name_ar'=>'لاتيه مثلج',   'description'=>'Chilled espresso over milk and ice',    'description_ar'=>'إسبريسو بارد مع الحليب والثلج', 'price'=>22],
            ['category_id'=>2,'name'=>'Cold Brew',    'name_ar'=>'كولد برو',     'description'=>'Slow-steeped 12 hours, smooth & bold',  'description_ar'=>'منقوع ببطء ١٢ ساعة، ناعم وقوي',  'price'=>25],
            ['category_id'=>2,'name'=>'Iced Cappuccino','name_ar'=>'كابتشينو مثلج','description'=>'Classic cappuccino served cold',       'description_ar'=>'كابتشينو كلاسيكي بارد',          'price'=>22],
            // Tea
            ['category_id'=>3,'name'=>'Karak Tea',    'name_ar'=>'كرك',          'description'=>'Spiced milk tea with cardamom',         'description_ar'=>'شاي بالحليب والهيل',            'price'=>10],
            ['category_id'=>3,'name'=>'Green Tea',    'name_ar'=>'شاي أخضر',     'description'=>'Japanese sencha, fresh and clean',      'description_ar'=>'سنشا ياباني طازج',              'price'=>12],
            ['category_id'=>3,'name'=>'Mint Tea',     'name_ar'=>'شاي بالنعناع', 'description'=>'Fresh mint with green tea',             'description_ar'=>'نعناع طازج مع الشاي الأخضر',    'price'=>12],
            // Pastries
            ['category_id'=>4,'name'=>'Croissant',    'name_ar'=>'كرواسون',      'description'=>'Buttery, flaky French pastry',          'description_ar'=>'معجنة فرنسية مقرمشة بالزبدة',   'price'=>15],
            ['category_id'=>4,'name'=>'Chocolate Muffin','name_ar'=>'مافن شوكولاتة','description'=>'Rich, moist chocolate muffin',         'description_ar'=>'مافن شوكولاتة طري وغني',        'price'=>18],
            ['category_id'=>4,'name'=>'Cheese Cake',  'name_ar'=>'تشيز كيك',     'description'=>'Classic New York style cheesecake',     'description_ar'=>'تشيز كيك نيويورك الكلاسيكي',    'price'=>25],
            // Juices
            ['category_id'=>5,'name'=>'Orange Juice', 'name_ar'=>'عصير برتقال',  'description'=>'Freshly squeezed orange juice',         'description_ar'=>'عصير برتقال معصور طازج',        'price'=>20],
            ['category_id'=>5,'name'=>'Mango Juice',  'name_ar'=>'عصير مانجو',   'description'=>'Pure fresh mango blend',                'description_ar'=>'مزيج مانجو طازج خالص',          'price'=>22],
        ];

        foreach ($items as $item) {
            MenuItem::create(array_merge($item, ['available' => true]));
        }
    }
}
