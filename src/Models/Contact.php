<?php

declare(strict_types = 1);

namespace Centrex\Addresses\Models;

use Centrex\Addresses\Factories\ContactFactory;
use Centrex\Addresses\Helpers\NameGenerator;
use Illuminate\Database\Eloquent\{Builder, Model, SoftDeletes};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, MorphTo};
use Illuminate\Support\Str;

/**
 * Class Contact
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
 * @property string|null $position
 * @property string|null $phone
 * @property string|null $mobile
 * @property string|null $fax
 * @property string|null $email
 * @property string|null $email_invoice
 * @property string|null $website
 * @property string|null $vat_id
 * @property string|null $notes
 * @property array|null $properties
 * @property int|null $address_id
 * @property-read string  $full_name
 * @property-read string  $full_name_rev
 * @property-read Model|null    $contactable
 * @property-read Address|null  $address
 *
 * @method static Builder|Contact flag(string $flag)
 */
class Contact extends Model
{
    use HasFactory;
    use SoftDeletes;

    /** {@inheritdoc} */
    protected $fillable = [
        'type',
        'gender',
        'title_before',
        'title_after',
        'external_id',

        'first_name',
        'middle_name',
        'last_name',

        'company',
        'extra',
        'position',
        'department',

        'phone',
        'mobile',
        'fax',
        'email',
        'email_invoice',
        'contact_email',
        'billing_email',
        'website',

        'vat_id',
        'instructions',
        'preferred_locale',
        'timezone',

        'notes',
        'properties',

        'address_id',

        'contactable_id',
        'contactable_type',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'properties' => 'array',

        'deleted_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('laravel-addresses.contacts.table', config('addresses.contacts.table', 'contacts'));
        $this->updateFillables();
    }

    /** {@inheritdoc} */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model): void {
            if ($model->getConnection()
                ->getSchemaBuilder()
                ->hasColumn($model->getTable(), 'uuid')) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    private function updateFillables(): void
    {
        $fillable = $this->fillable;
        $flags = config('laravel-addresses.contacts.flags', config('addresses.contacts.flags', ['public', 'primary']));
        $columns = array_map(static fn (string $flag): string => 'is_' . $flag, $flags);

        $this->fillable(array_merge($fillable, $columns));
    }

    public function contactable(): MorphTo
    {
        return $this->morphTo();
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(config('laravel-addresses.addresses.model', config('addresses.addresses.model', Address::class)));
    }

    public static function getValidationRules(): array
    {
        return config('laravel-addresses.contacts.rules', config('addresses.contacts.rules', []));
    }

    public function getFullNameAttribute(?bool $with_salutation = null, ?bool $with_titles = null, ?bool $with_name_reversed = null): string
    {
        $generator = (new NameGenerator(
            $this->gender,
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->title_before,
            $this->title_after,
        ));

        if ($with_salutation) {
            $generator->withSalutation();
        }

        if ($with_titles) {
            $generator->withTitles();
        }

        if ($with_name_reversed) {
            $generator->withNameReversed();
        }

        return $generator->toString();
    }

    public function getFullNameRevAttribute(?bool $show_salutation = null, ?bool $with_titles = null): string
    {
        return $this->getFullNameAttribute($show_salutation, $with_titles, true);
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
            ->where('contactable_type', $owner->getMorphClass())
            ->where('contactable_id', $owner->getKey());
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $query) use ($term): void {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term) . '%';

            $query->where('first_name', 'like', $like)
                ->orWhere('middle_name', 'like', $like)
                ->orWhere('last_name', 'like', $like)
                ->orWhere('company', 'like', $like)
                ->orWhere('position', 'like', $like)
                ->orWhere('department', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('contact_email', 'like', $like)
                ->orWhere('mobile', 'like', $like)
                ->orWhere('phone', 'like', $like)
                ->orWhere('external_id', 'like', $like);
        });
    }

    protected static function newFactory(): ContactFactory
    {
        return new ContactFactory();
    }
}
