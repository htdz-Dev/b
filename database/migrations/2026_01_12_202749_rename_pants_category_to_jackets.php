<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('categories')
            ->where('slug', 'pants')
            ->update([
                    'name' => 'Jackets',
                    'slug' => 'jackets',
                    'description' => 'Premium urban jackets and outerwear',
                ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('categories')
            ->where('slug', 'jackets')
            ->update([
                    'name' => 'Pants',
                    'slug' => 'pants',
                    'description' => 'Urban style pants and joggers',
                ]);
    }
};
