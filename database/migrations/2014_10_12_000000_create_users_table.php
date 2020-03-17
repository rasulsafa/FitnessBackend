<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('date_of_birth');
            $table->string('password');
            $table->integer('weight');
            $table->integer('height');
            $table->string('sex');
            $table->double('latitude', 15, 10)->nullable();
            $table->double('longitude', 15, 10)->nullable();
            $table->string('home_address');
            $table->string("weight_goal");
            
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('workouts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string("name");
            $table->string("description");
            
            $table->boolean('completed')->default(false);
            $table->boolean('in_progress')->default(false);
            $table->string('date_completed')->nullable();
            $table->integer('calories_burnt')->default(0);
            $table->decimal('exercise_equation_multiplier', 10, 5);
            $table->boolean('outdoor_activity');
            
            $table->integer('progress')->default(0);
            $table->integer('goal_amount');
            $table->string('unit');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
