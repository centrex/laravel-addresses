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
        $this->table = config('laravel-addresses.contacts.table', config('addresses.contacts.table', 'contacts'));
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('external_id', 150)->nullable();

            $table->string('type', 40)->default('default')->index();
            $table->string('gender', 20)->nullable();

            $table->string('title_before', 40)->nullable();
            $table->string('title_after', 40)->nullable();
            $table->string('first_name', 100)->nullable();
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();

            $table->string('company', 180)->nullable();
            $table->string('extra')->nullable();
            $table->string('position', 120)->nullable();
            $table->string('department', 120)->nullable();

            $table->string('phone', 32)->nullable();
            $table->string('mobile', 32)->nullable();
            $table->string('fax', 32)->nullable();
            $table->string('email', 191)->nullable();
            $table->string('email_invoice', 191)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('billing_email')->nullable();
            $table->text('instructions')->nullable();
            $table->string('website', 100)->nullable();
            $table->string('vat_id')->nullable();
            $table->string('preferred_locale', 20)->nullable();
            $table->string('timezone', 80)->nullable();

            $table->unsignedBigInteger('address_id')
                ->nullable()
                ->index();

            $table->nullableMorphs('contactable');

            foreach (config('laravel-addresses.contacts.flags', config('addresses.contacts.flags', ['public', 'primary'])) as $flag) {
                $table->boolean('is_' . $flag)->default(false)->index();
            }

            $table->longText('notes')->nullable();
            $table->json('properties')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('address_id')
                ->references('id')
                ->on(config('laravel-addresses.addresses.table', config('addresses.addresses.table', 'addresses')))
                ->nullOnDelete();
            $table->index(['contactable_type', 'contactable_id', 'type'], 'contacts_owner_type_idx');
            $table->index(['contactable_type', 'contactable_id', 'deleted_at'], 'contacts_owner_active_idx');
            $table->index(['email', 'mobile'], 'contacts_email_mobile_idx');
            $table->unique(['contactable_type', 'contactable_id', 'external_id'], 'contacts_owner_external_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->table);
    }
};
