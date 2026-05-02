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
        $this->table = config('laravel-addresses.addresses.table', config('addresses.addresses.table', 'addresses'));
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
            $table->string('label', 120)->nullable();

            $table->string('gender', 20)->nullable();
            $table->string('title_before', 40)->nullable();
            $table->string('title_after', 40)->nullable();
            $table->string('first_name', 100)->nullable();
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('company', 180)->nullable();
            $table->string('extra', 180)->nullable();

            $table->string('street', 180)->nullable();
            $table->string('street_extra', 180)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('state', 120)->nullable();
            $table->string('district', 120)->nullable();
            $table->string('region', 120)->nullable();
            $table->string('post_code', 30)->nullable();
            $table->unsignedInteger('country_id')->nullable()->index();
            $table->char('country_code', 2)->nullable()->index();

            $table->string('vat_id', 80)->nullable();
            $table->string('eori_id', 80)->nullable();
            $table->string('contact_phone', 60)->nullable();
            $table->string('contact_email', 191)->nullable();
            $table->string('billing_email', 191)->nullable();
            $table->text('instructions')->nullable();
            $table->text('notes')->nullable();

            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->timestamp('geocoded_at')->nullable();
            $table->string('geocode_provider', 40)->nullable();
            $table->string('validation_status', 40)->default('unverified')->index();
            $table->json('properties')->nullable();

            $table->nullableMorphs('addressable');
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();

            foreach (config('laravel-addresses.addresses.flags', config('addresses.addresses.flags', ['public', 'primary', 'billing', 'shipping'])) as $flag) {
                $table->boolean('is_' . $flag)->default(false)->index();
            }

            $table->timestamps();
            $table->softDeletes();

            $table->index(['addressable_type', 'addressable_id', 'type'], 'addresses_owner_type_idx');
            $table->index(['addressable_type', 'addressable_id', 'deleted_at'], 'addresses_owner_active_idx');
            $table->index(['country_id', 'state', 'city'], 'addresses_location_idx');
            $table->index(['post_code', 'country_id'], 'addresses_post_country_idx');
            $table->unique(['addressable_type', 'addressable_id', 'external_id'], 'addresses_owner_external_unique');
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
