<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('selection_periods', function (Blueprint $table) {
            $table->enum('aggregation_method', ['average', 'owa_most'])->default('average')->after('status');
            $table->decimal('owa_alpha', 5, 2)->default(2.00)->after('aggregation_method');
            $table->timestamp('aggregation_computed_at')->nullable()->after('owa_alpha');
        });
    }

    public function down(): void
    {
        Schema::table('selection_periods', function (Blueprint $table) {
            $table->dropColumn(['aggregation_method', 'owa_alpha', 'aggregation_computed_at']);
        });
    }
};
