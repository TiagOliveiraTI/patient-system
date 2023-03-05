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
        Schema::create('addresses', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string("cep");
            $table->string("logradouro");
            $table->string("complemento");
            $table->string("bairro");
            $table->string("localidade");
            $table->string("uf");
            $table->string("ibge");
            $table->string("gia");
            $table->string("ddd");
            $table->string("siafi");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address');
    }
};
