<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('barcode', 100)->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('cost', 12, 2)->default(0);
            $table->decimal('stock_qty', 12, 2)->default(0);
            $table->decimal('min_stock', 12, 2)->default(0);
            $table->smallInteger('status')->default(1);
            $table->boolean('has_variants')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('status');
            $table->unique(['tenant_id', 'barcode']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
