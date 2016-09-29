<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBannersTargetedSocietiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banners_targeted_societies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('banner_id')->unsigned();
            $table->integer('society_id')->unsigned();
            $table->timestamps();


            $table->foreign('banner_id')
                ->references('id')->on('banners')
                ->onDelete('cascade');

            $table->foreign('society_id')
                ->references('id')->on('societies')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('banners_targeted_societies');
    }
}
