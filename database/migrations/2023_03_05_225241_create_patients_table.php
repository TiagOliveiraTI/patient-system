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
        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string("cpf")->unique();
            $table->string("photo");
            $table->string("name");
            $table->string("mom_name");
            $table->dateTimeTz("birth_date");
            $table->string("cns");

            $table->foreignUuid('address_id')
            ->references('uuid')
            ->on('addresses')
            ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
