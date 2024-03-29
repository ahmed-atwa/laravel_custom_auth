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
        Schema::create('tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('token', 250)->unique('unique_token');
            //don't restrict the query to user-agent and ip-address,
            //instead use this only to display logged devices with details if needed.
            $table->string('agent', 250)->nullable();  //browser user-agent
            $table->string('ip', 250); //ip address

            //$table->dateTime('created_at');
            $table->timestamps();

            $table->foreign('user_id', 'fk_user')
					->references('id')->on('users')
					->onUpdate('cascade')
					->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tokens');
    }
};
