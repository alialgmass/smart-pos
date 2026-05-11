<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')
                ->nullable()
                ->after('id')
                ->index();

            $table->boolean('is_active')
                ->default(true)
                ->after('password');

            $table->index(['tenant_id', 'is_active'], 'users_tenant_active_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_tenant_active_index');
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
            $table->dropColumn('is_active');
        });
    }
};
