<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_listings', function (Blueprint $table) {
            $table->id();
            $table->string('google_calendar_event_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('offered_coins')->default(1);
            $table->longText('message')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->unsignedBigInteger('accepted_by')->nullable();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('accepted_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_listings');
    }
};
