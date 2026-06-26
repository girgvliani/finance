<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add a dedicated calendar date column.
        Schema::table('transactions', function (Blueprint $table) {
            $table->date('event_date')->nullable()->after('amount');
        });

        // Broaden the type enum and allow nullable amount (for notes).
        // Schema::change() keeps this portable across MySQL and SQLite.
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('type', ['income', 'expense', 'payment', 'investment', 'note'])->change();
            $table->decimal('amount', 10, 2)->nullable()->change();
        });

        // Backfill event_date from deadline (or created_at) so existing rows appear on the calendar.
        DB::table('transactions')->whereNull('event_date')->update([
            'event_date' => DB::raw('COALESCE(deadline, DATE(created_at))'),
        ]);
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('event_date');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->nullable(false)->change();
            $table->enum('type', ['income', 'expense'])->change();
        });
    }
};
