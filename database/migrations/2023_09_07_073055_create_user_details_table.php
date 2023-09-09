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
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_authentication_id');
            $table->foreign('user_authentication_id')
                ->references('id')
                ->on('user_authentication')
                ->onDelete('cascade');
            $table->string('name');
            $table->string('other_details_column1');
            $table->string('other_details_column2');
            $table->string('other_details_column3');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};
