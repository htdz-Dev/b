<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // SQLite doesn't support modifying columns directly
        // We need to recreate the table or use a workaround

        // For SQLite, we'll drop the check constraint by recreating
        // For now, let's use a simple approach: change column to string
        Schema::table('orders', function (Blueprint $table) {
            // SQLite workaround - we'll just change it to a string
        });

        // Direct SQL to remove constraint (SQLite specific workaround)
        // Create new table, copy data, drop old, rename new
        DB::statement('PRAGMA foreign_keys=off;');

        DB::statement('
            CREATE TABLE orders_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                order_number VARCHAR(255) UNIQUE,
                status VARCHAR(50) DEFAULT "pending",
                subtotal DECIMAL(10,2),
                shipping_cost DECIMAL(10,2) DEFAULT 0,
                total DECIMAL(10,2),
                payment_method VARCHAR(50) DEFAULT "cod",
                payment_status VARCHAR(50) DEFAULT "pending",
                chargily_checkout_id VARCHAR(255),
                shipping_name VARCHAR(255),
                shipping_phone VARCHAR(255),
                shipping_address VARCHAR(255),
                shipping_city VARCHAR(255),
                shipping_wilaya VARCHAR(255),
                shipping_postal_code VARCHAR(255),
                notes TEXT,
                created_at TIMESTAMP,
                updated_at TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        ');

        DB::statement('
            INSERT INTO orders_new 
            SELECT id, user_id, order_number, status, subtotal, shipping_cost, total, 
                   payment_method, payment_status, chargily_checkout_id, shipping_name, 
                   shipping_phone, shipping_address, shipping_city, shipping_wilaya, 
                   shipping_postal_code, notes, created_at, updated_at 
            FROM orders
        ');

        DB::statement('DROP TABLE orders');
        DB::statement('ALTER TABLE orders_new RENAME TO orders');

        DB::statement('PRAGMA foreign_keys=on;');
    }

    public function down(): void
    {
        // Cannot easily reverse this
    }
};
