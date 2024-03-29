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
        $this->table = config('laravel_addresses.contacts.table', 'contacts');
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

            $table->string('type', 20)->default('default');
            $table->string('gender', 1)->nullable();

            $table->string('title_before', 20)->nullable();
            $table->string('title_after', 20)->nullable();
            $table->string('first_name', 20)->nullable();
            $table->string('middle_name', 20)->nullable();
            $table->string('last_name', 20)->nullable();

            $table->string('company', 60)->nullable();
            $table->string('extra')->nullable();
            $table->string('position', 60)->nullable();

            $table->string('phone', 32)->nullable();
            $table->string('mobile', 32)->nullable();
            $table->string('fax', 32)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('billing_email')->nullable();
            $table->string('instructions')->nullable();
            $table->string('website', 100)->nullable();
            $table->string('vat_id')->nullable();

            $table->integer('address_id')
                ->nullable()
                ->unsigned()
                ->references('id')
                ->on(config('laravel_addresses.addresses.table', 'addresses'));

            $table->nullableMorphs('contactable');

            foreach (config('laravel_addresses.contacts.flags', ['public', 'primary']) as $flag) {
                $table->boolean('is_' . $flag)->default(false)->index();
            }

            $table->longText('notes')->nullable();
            $table->text('properties')->nullable();

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
