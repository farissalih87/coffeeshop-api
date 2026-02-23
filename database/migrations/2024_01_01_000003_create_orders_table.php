<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->enum('location', ['salon', 'reception']);
            $table->text('notes')->nullable();
            $table->decimal('total', 8, 2)->default(0);
            $table->enum('status', ['pending','preparing','ready','delivered','cancelled'])->default('pending');
            $table->string('lang', 5)->default('en');
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('menu_item_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price', 8, 2);
            $table->decimal('subtotal', 8, 2);
            $table->timestamps();
        });

        
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
