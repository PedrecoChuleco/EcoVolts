<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('cpf', 14)->nullable()->unique()->after('email');
            $table->string('phone', 20)->nullable()->after('cpf');
            $table->string('cep', 9)->nullable()->after('phone');
            $table->string('city')->nullable()->after('cep');
            $table->string('neighborhood')->nullable()->after('city');
            $table->string('street')->nullable()->after('neighborhood');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['cpf']);
            $table->dropColumn([
                'cpf',
                'phone',
                'cep',
                'city',
                'neighborhood',
                'street',
            ]);
        });
    }
};
