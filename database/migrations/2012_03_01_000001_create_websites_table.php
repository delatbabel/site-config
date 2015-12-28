<?php
/**
 * Class CreateWebsitesTable
 */

use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateWebsitesTable
 *
 * Migration script that creates the websites table.
 */
class CreateWebsitesTable extends Migration
{
    /**
     * Make changes to the database.
     *
     * @return void
     */
    public function up()
    {
        /** @var \Illuminate\Database\Schema\Blueprint $table */
        Schema::create('websites', function ($table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->string('http_host', 255)->unique();
            $table->string('environment')->nullable();
            $table->string('created_by', 255)->nullable();
            $table->string('updated_by', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Revert the changes to the database.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('websites');
    }
}
