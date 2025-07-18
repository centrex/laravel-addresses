<?php

declare(strict_types = 1);

namespace Centrex\Addresses\Traits;

use Centrex\Addresses\Models\Country;
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class HasCountry
 *
 * @property int|null $country_id
 * @property string $country_code
 * @property-read Country|null  $country
 *
 * @method static Builder|Model byCountry(string $value)
 */
trait HasCountry
{
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function getCountryCodeAttribute(): string
    {
        if ($country = $this->country) {
            return $country->iso_3166_2;
        }

        return '';
    }

    public function scopeByCountry(Builder $query, Country|int $country): Builder
    {
        $country = is_int($country) ? $country : $country->id;

        return $query->whereHas('country', function (Builder $q) use ($country): void {
            $q->where('id', $country);
        });
    }
}
