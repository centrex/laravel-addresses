<?php

declare(strict_types = 1);

namespace Centrex\Addresses\Models;

use Centrex\Addresses\Factories\AddressFactory;
use Centrex\Addresses\Helpers\NameGenerator;
use Centrex\Addresses\Traits\HasCountry;
use Illuminate\Database\Eloquent\{Builder, Collection, Model, SoftDeletes};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, MorphTo};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Class Address
 *
 * @property-read int          $id
 * @property-read string|null  $uuid
 * @property string|null $gender
 * @property string|null $title_before
 * @property string|null $title_after
 * @property string|null $first_name
 * @property string|null $middle_name
 * @property string|null $last_name
 * @property string|null $company
 * @property string|null $extra
 * @property string|null $street
 * @property string|null $street_extra
 * @property string|null $city
 * @property string|null $state
 * @property string|null $post_code
 * @property string|null $vat_id
 * @property string|null $eori_id
 * @property string|null $contact_phone
 * @property string|null $contact_email
 * @property string|null $billing_email
 * @property string|null $instructions
 * @property string|null $notes
 * @property array|null $properties
 * @property string|null $lat
 * @property string|null $lng
 * @property bool $is_primary
 * @property bool $is_billing
 * @property bool $is_shipping
 * @property-read string  $country_name
 * @property-read string  $route
 * @property-read string  $street_number
 * @property-read Model|null            $addressable
 * @property-read Collection|Contact[]  $contacts
 * @property-read Model|null            $user
 *
 * @method static Builder|Address flag(string $flag)
 */
class Address extends Model
{
    use HasCountry;
    use HasFactory;
    use SoftDeletes;

    /** {@inheritdoc} */
    protected $fillable = [
        'gender',
        'title_before',
        'title_after',
        'type',
        'label',

        'first_name',
        'middle_name',
        'last_name',

        'company',
        'extra',

        'street',
        'street_extra',
        'city',
        'state',
        'district',
        'region',
        'post_code',
        'country_id',
        'country_code',

        'vat_id',
        'eori_id',
        'contact_phone',
        'contact_email',
        'billing_email',

        'instructions',
        'notes',
        'properties',
        'external_id',

        'lat',
        'lng',
        'geocoded_at',
        'geocode_provider',
        'validation_status',

        'addressable_type',
        'addressable_id',

        'user_id',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'properties' => 'array',

        'lat'         => 'decimal:7',
        'lng'         => 'decimal:7',
        'geocoded_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('laravel-addresses.addresses.table', config('addresses.addresses.table', 'addresses'));
        $this->updateFillables();
    }

    /** {@inheritdoc} */
    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($model): void {
            if ($model->getConnection()
                ->getSchemaBuilder()
                ->hasColumn($model->getTable(), 'uuid')) {
                $model->uuid = (string) Str::uuid();
            }
        });

        static::saving(function ($address): void {
            if ($address->shouldGeocode()) {
                $address->geocode();
            }
        });
    }

    private function updateFillables(): void
    {
        $fillable = $this->fillable;
        $flags = config('laravel-addresses.addresses.flags', config('addresses.addresses.flags', ['public', 'primary', 'billing', 'shipping']));
        $columns = array_map(static fn (string $flag): string => 'is_' . $flag, $flags);

        $this->fillable(array_merge($fillable, $columns));
    }

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(config('laravel-addresses.contacts.model', config('addresses.contacts.model', Contact::class)));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\\Models\\User'));
    }

    public static function getValidationRules(): array
    {
        $rules = config('laravel-addresses.addresses.rules', config('addresses.addresses.rules', [
            'street'       => 'required|string|min:3|max:60',
            'street_extra' => 'nullable|string|min:3|max:60',
            'city'         => 'required|string|min:3|max:60',
            'state'        => 'nullable|string|min:3|max:60',
            'post_code'    => 'required|min:4|max:10|AlphaDash',
            'country_id'   => 'required|integer',
        ]));

        foreach (config('laravel-addresses.addresses.flags', config('addresses.addresses.flags', ['public', 'primary', 'billing', 'shipping'])) as $flag) {
            $rules['is_' . $flag] = 'boolean';
        }

        return $rules;
    }

    public function geocode(): self
    {
        if (!($query = $this->getQueryString()) || !($key = config('services.google.maps.key', ''))) {
            return $this;
        }

        $response = rescue(
            static fn () => Http::timeout(3)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => urldecode($query),
                'sensor'  => 'false',
                'key'     => $key,
            ]),
            report: false,
        );

        if ($response?->successful()) {
            $geometry = data_get($response->json(), 'results.0.geometry.location');

            if ($geometry !== null) {
                $this->lat = (string) data_get($geometry, 'lat');
                $this->lng = (string) data_get($geometry, 'lng');
                $this->geocoded_at = now();
                $this->geocode_provider = 'google';
            }
        }

        return $this;
    }

    public function shouldGeocode(): bool
    {
        if (!config('laravel-addresses.addresses.geocode', config('addresses.addresses.geocode', false))) {
            return false;
        }

        return $this->isDirty(['street', 'street_extra', 'city', 'state', 'post_code', 'country_id'])
            || blank($this->lat)
            || blank($this->lng);
    }

    public function getQueryString(): string
    {
        $query = [];
        $query[] = $this->street ?: '';
        //  $query[] = $this->street_extra ?: '';
        $query[] = $this->city ?: '';
        $query[] = $this->state ?: '';
        $query[] = $this->post_code ?: '';
        $query[] = $this->country_name ?: '';

        $query = trim(implode(',', array_filter($query)));

        return urlencode($query);
    }

    public function getArray(): array
    {
        $two = [];
        $two[] = $this->post_code ?: '';
        $two[] = $this->city ?: '';
        $two[] = $this->state ? '(' . $this->state . ')' : '';

        $address = $this->getAddresseeLines();
        $address[] = $this->street ?: '';
        $address[] = $this->street_extra ?: '';
        $address[] = implode(' ', array_filter($two));
        $address[] = $this->country_name ?: $this->country_code;

        if (($address = array_filter($address)) !== []) {
            return $address;
        }

        return [];
    }

    public function getHtml(): string
    {
        if ($address = $this->getArray()) {
            return '<address>' . implode('<br />', array_filter($address)) . '</address>';
        }

        return '';
    }

    public function getLine(string $glue = ', '): string
    {
        if ($address = $this->getArray()) {
            return implode($glue, array_filter($address));
        }

        return '';
    }

    public function formattedAddress(string $glue = ', '): string
    {
        return $this->getLine($glue);
    }

    public function getCountryNameAttribute(): string
    {
        if ($this->country) {
            return $this->country->name;
        }

        return '';
    }

    public function getCountryCodeAttribute(mixed $value = null): string
    {
        if (is_int($value)) {
            return $this->countryCode($value);
        }

        return $this->countryCode(2, is_string($value) ? $value : null);
    }

    public function countryCode(int $digits = 2, ?string $rawValue = null): string
    {
        $raw = strtoupper((string) ($rawValue ?? $this->attributes['country_code'] ?? ''));

        if ($raw !== '') {
            return $digits === 3 ? $raw : substr($raw, 0, 2);
        }

        if (!$this->country) {
            return '';
        }

        if ($digits === 3) {
            return $this->country->iso_3166_3;
        }

        return $this->country->iso_3166_2;
    }

    public function getRouteAttribute(): string
    {
        if (preg_match('/(\D+)\s?(.+)/i', (string) $this->street, $result)) {
            return trim($result[1]);
        }

        return '';
    }

    public function getStreetNumberAttribute(): string
    {
        if (preg_match('/(\D+)\s?(.+)/i', (string) $this->street, $result)) {
            return trim($result[2]);
        }

        return '';
    }

    public function getAddresseeLines(): array
    {
        $name = (new NameGenerator(
            $this->gender,
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->title_before,
            $this->title_after,
        ))->forShippingLabel()->toString();

        return array_filter([
            $this->company,
            $this->extra,
            $name,
        ]);
    }

    public function scopeFlag(Builder $query, string $flag): Builder
    {
        return $query->where('is_' . $flag, true);
    }

    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeForOwner(Builder $query, Model $owner): Builder
    {
        return $query
            ->where('addressable_type', $owner->getMorphClass())
            ->where('addressable_id', $owner->getKey());
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $query) use ($term): void {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term) . '%';

            $query->where('street', 'like', $like)
                ->orWhere('street_extra', 'like', $like)
                ->orWhere('city', 'like', $like)
                ->orWhere('state', 'like', $like)
                ->orWhere('district', 'like', $like)
                ->orWhere('region', 'like', $like)
                ->orWhere('post_code', 'like', $like)
                ->orWhere('company', 'like', $like)
                ->orWhere('external_id', 'like', $like);
        });
    }

    /** @deprecated use scopeFlag('primary') instead */
    public function scopePrimary(Builder $query): Builder
    {
        return $query->where('is_primary', true);
    }

    /** @deprecated use scopeFlag('billing') instead */
    public function scopeBilling(Builder $query): Builder
    {
        return $query->where('is_billing', true);
    }

    /** @deprecated use scopeFlag('shipping') instead */
    public function scopeShipping(Builder $query): Builder
    {
        return $query->where('is_shipping', true);
    }

    protected static function newFactory(): AddressFactory
    {
        return new AddressFactory();
    }
}
