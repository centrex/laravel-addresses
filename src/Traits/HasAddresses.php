<?php

declare(strict_types = 1);

namespace Centrex\Addresses\Traits;

use Centrex\Addresses\Exceptions\FailedValidationException;
use Centrex\Addresses\Models\{Address, Country};
use Exception;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\{Collection, Model};
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Throwable;

/**
 * @property-read Collection|Address[] $addresses
 */
trait HasAddresses
{
    public function addresses(): MorphMany
    {
        /** @var Model $this */
        return $this->morphMany(
            config('laravel-addresses.addresses.model', Address::class),
            'addressable',
        );
    }

    public function hasAddresses(): bool
    {
        return $this->relationLoaded('addresses')
            ? $this->addresses->isNotEmpty()
            : $this->addresses()->exists();
    }

    /** @throws Exception */
    public function addAddress(array $attributes): Address|Model
    {
        $attributes = $this->loadAddressAttributes($attributes);

        return $this->addresses()->create($attributes);
    }

    /** @throws Exception */
    public function upsertAddress(array $attributes, array $uniqueBy = ['type']): Address|Model
    {
        $attributes = $this->loadAddressAttributes($attributes);
        $identity = array_intersect_key($attributes, array_flip($uniqueBy));

        if ($identity === []) {
            $identity = ['type' => $attributes['type'] ?? 'default'];
        }

        return $this->addresses()->updateOrCreate($identity, $attributes);
    }

    /** @throws Exception */
    public function updateAddress(Address $address, array $attributes): bool
    {
        if ($address->addressable_id !== $this->getKey() || $address->addressable_type !== $this->getMorphClass()) {
            throw new Exception('Address does not belong to this model');
        }

        $attributes = $this->validateOnlyGivenAttributes($attributes);

        return $address->update($attributes);
    }

    /** @throws Exception */
    public function deleteAddress(Address $address): bool
    {
        if ($address->addressable_id !== $this->getKey() || $address->addressable_type !== $this->getMorphClass()) {
            throw new Exception('Address does not belong to this model');
        }

        return $address->delete();
    }

    public function flushAddresses(): bool
    {
        return $this->addresses()->delete();
    }

    public function getAddress(?string $flag = null, string $direction = 'desc', bool $strict = false): ?Address
    {
        if (!$this->hasAddresses()) {
            return null;
        }

        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        if ($flag !== null) {
            $address = $this->getAddressByFlag($flag, $direction);

            if ($address || $strict) {
                return $address;
            }

            return $this->getFallbackAddress($flag, $direction);
        }

        return $this->getDefaultAddress($direction);
    }

    protected function getAddressByFlag(string $flag, string $direction): ?Address
    {
        return $this->addresses()
            ->flag($flag)
            ->latest('updated_at')
            ->first();
    }

    protected function getFallbackAddress(string $originalFlag, string $direction): ?Address
    {
        $fallbackOrder = config('laravel-addresses.addresses.flags', []);
        $flagIndex = array_search($originalFlag, $fallbackOrder);

        if ($flagIndex === false || $flagIndex === 0) {
            return null;
        }

        $tryFlag = $fallbackOrder[$flagIndex - 1] ?? null;

        return $tryFlag ? $this->getAddress($tryFlag, $direction) : null;
    }

    protected function getDefaultAddress(string $direction): ?Address
    {
        return $direction === 'DESC'
            ? $this->addresses()->first()
            : $this->addresses()->last();
    }

    /** @throws FailedValidationException */
    public function loadAddressAttributes(array $attributes): array
    {
        $countryCode = $attributes['country'] ?? $attributes['country_code'] ?? null;

        if (empty($countryCode) && empty($attributes['country_id'])) {
            $this->validateAddressAttributes($attributes);

            return $attributes;
        }

        $country = $countryCode ? $this->findCountryByCode((string) $countryCode) : null;

        if ($countryCode && !$country?->id) {
            $attributes['country_code'] = strtoupper(substr((string) $countryCode, 0, 2));
        }

        if ($country?->id) {
            $attributes['country_id'] = $country->id;
            $attributes['country_code'] = strtoupper((string) $countryCode);
        }

        unset($attributes['country']);

        $this->validateAddressAttributes($attributes);

        return $attributes;
    }

    /** @throws FailedValidationException */
    protected function validateAddressAttributes(array $attributes): void
    {
        $validator = $this->validateAddress($attributes);

        if ($validator->fails()) {
            throw new FailedValidationException(
                '[Addresses] ' . $validator->errors()->first(),
            );
        }
    }

    public function validateAddress(array $attributes): Validator
    {
        $model = config('laravel-addresses.addresses.model', Address::class);
        $rules = (new $model())->getValidationRules();

        return validator($attributes, $rules);
    }

    public function validateOnlyGivenAttributes(array $attributes): array
    {
        $model = config('laravel-addresses.addresses.model', Address::class);
        $rules = (new $model())->getValidationRules();

        // Filter rules to only include keys present in the attributes
        $filteredRules = array_intersect_key($rules, $attributes);

        // Validate only the provided attributes
        $validator = validator($attributes, $filteredRules);

        if ($validator->fails()) {
            throw new FailedValidationException(
                '[Validation] ' . $validator->errors()->first(),
            );
        }

        return $attributes;
    }

    public function findCountryByCode(string $countryCode): ?Country
    {
        try {
            return Country::whereCountryCode($countryCode)->first();
        } catch (Throwable) {
            return null;
        }
    }

    /** @deprecated */
    public function getPrimaryAddress(string $direction = 'desc'): ?Address
    {
        return $this->getAddress('primary', $direction, true);
    }

    /** @deprecated */
    public function getBillingAddress(string $direction = 'desc'): ?Address
    {
        return $this->getAddress('billing', $direction, true);
    }

    /** @deprecated */
    public function getShippingAddress(string $direction = 'desc'): ?Address
    {
        return $this->getAddress('shipping', $direction, true);
    }
}
