<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (!Schema::hasColumn('usuarios', 'promo_email')) {
                $table->boolean('promo_email')->default(false)->after('genero');
            }
            if (!Schema::hasColumn('usuarios', 'promo_whatsapp')) {
                $table->boolean('promo_whatsapp')->default(false)->after('promo_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn(['promo_email', 'promo_whatsapp']);
        });
    }
};
