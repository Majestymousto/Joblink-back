<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('employers', function (Blueprint $table) {
        $table->string('nif')->nullable()->after('numero_identification');
    });
}

public function down(): void
{
    Schema::table('employers', function (Blueprint $table) {
        $table->dropColumn('nif');
    });
}
};
