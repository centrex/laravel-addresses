<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Table names.
     *
     * @var string The main table name for this migration.
     */
    protected $table;

    /** Create a new migration instance. */
    public function __construct()
    {
        $this->table = config('laravel_addresses.addresses.table', 'addresses');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid')->nullable();

            $table->string('street', 60)->nullable();
            $table->string('street_extra', 60)->nullable();
            $table->string('city', 60)->nullable();
            $table->string('state', 60)->nullable();
            $table->string('post_code', 10)->nullable();
            $table->integer('country_id')->nullable()->unsigned()->index();
            $table->string('notes')->nullable();

            $table->float('lat', 10, 6)->nullable();
            $table->float('lng', 10, 6)->nullable();
            $table->text('properties')->nullable();

            $table->nullableMorphs('addressable');
            $table->foreignId('user_id')->index()->constrained()->onDelete('cascade');

            foreach (config('laravel_addresses.addresses.flags', ['public', 'primary', 'billing', 'shipping']) as $flag) {
                $table->boolean('is_' . $flag)->default(false)->index();
            }

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->table);
    }
};
