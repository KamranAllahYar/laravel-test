<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCinemaSchema extends Migration
{
    /** ToDo: Create a migration that creates all tables for the following user stories
     *
     * For an example on how a UI for an api using this might look like, please try to book a show at https://in.bookmyshow.com/.
     * To not introduce additional complexity, please consider only one cinema.
     *
     * Please list the tables that you would create including keys, foreign keys and attributes that are required by the user stories.
     *
     * ## User Stories
     **Movie exploration**
     * As a user I want to see which films can be watched and at what times
     * As a user I want to only see the shows which are not booked out
     **Show administration**
     * As a cinema owner I want to run different films at different times
     * As a cinema owner I want to run multiple films at the same time in different showrooms
     **Pricing**
     * As a cinema owner I want to get paid differently per show
     * As a cinema owner I want to give different seat types a percentage premium, for example 50 % more for vip seat
     **Seating**
     * As a user I want to book a seat
     * As a user I want to book a vip seat/couple seat/super vip/whatever
     * As a user I want to see which seats are still available
     * As a user I want to know where I'm sitting on my ticket
     * As a cinema owner I dont want to configure the seating for every show
     */
    public function up()
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index();
            $table->string('genre')->index();
            $table->text('poster');
            $table->timestamp('release_date')->index();
            $table->timestamps();
        });

        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index();
            $table->boolean('is_vip')->index()->default(0);
            $table->float('additional_percentage')->index()->default(0.0);
            $table->timestamps();
        });


        Schema::create('showrooms', function (Blueprint $table) {
            $table->id();
            $table->string('cinema')->index(); //as per single cinema
            $table->string('title')->index();
            $table->string('location')->index()->nullable();
            $table->timestamps();
            $table->unique(['cinema', 'title']);
        });


        Schema::create('showroom_seats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('showroom_id');
            $table->foreign('showroom_id')->references('id')->on('showrooms')->onDelete('cascade');
            $table->unsignedBigInteger('seat_id');
            $table->foreign('seat_id')->references('id')->on('seats')->onDelete('cascade');
            $table->string('seat_no', 20)->index();
            $table->char('seat_row', 5)->index();
            $table->boolean('is_booked')->index()->default(0);
            $table->timestamps();
            $table->unique(['showroom_id', 'seat_row', 'seat_no', 'seat_id']);
        });

        Schema::create('show_timings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movie_id');
            $table->foreign('movie_id')->references('id')->on('movies')->onDelete('cascade');
            $table->timestamp('show_time')->index();
            $table->double('price')->index()->default(0);
            $table->timestamps();
        });
        Schema::create('shows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('show_timings_id');
            $table->foreign('show_timings_id')->references('id')->on('show_timings')->onDelete('cascade');
            $table->unsignedBigInteger('showroom_seat_id');
            $table->foreign('showroom_seat_id')->references('id')->on('showroom_seats')->onDelete('cascade');
            $table->double('price')->index()->default(0);
            $table->timestamps();
        });
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('show_timings_id');
            $table->foreign('show_timings_id')->references('id')->on('show_timings')->onDelete('cascade');
            $table->string('payment_status')->index()->default('unpaid');
            $table->string('booking_status')->index()->default('booked');
            //rest of the data goes here for booking details like attaching user info etc
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
        Schema::dropIfExists('movies');
        Schema::dropIfExists('seats');
        Schema::dropIfExists('showrooms');
        Schema::dropIfExists('showroom_seats');
        Schema::dropIfExists('show_timings');
        Schema::dropIfExists('shows');
        Schema::dropIfExists('bookings');
    }
}
