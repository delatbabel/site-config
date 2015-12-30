<?php
/**
 * Class CreateConfigTable
 */

use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateConfigTable
 *
 * Migration script that creates the configuration table.
 */
class CreateConfigTable extends Migration
{
    /**@var string */
    protected $tableName;

    public function __construct()
    {
        $this->tableName = 'configs';
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /** @var \Illuminate\Database\Schema\Blueprint $table */
        Schema::create($this->tableName, function ($table) {
            $table->increments('id');
            $table->integer('website_id')->unsigned()->nullable();
            $table->string('environment')->nullable();
            $table->string('group')->default('config')->index();
            $table->string('key');
            $table->longText('value')->nullable();
            $table->string('type');
            $table->timestamps();

            $table->unique(array('website_id', 'environment', 'group', 'key'));

            $table->foreign('website_id')
                ->references('id')->on('websites')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop($this->tableName);
    }
}
