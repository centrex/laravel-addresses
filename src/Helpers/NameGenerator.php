<?php

declare(strict_types = 1);

namespace Centrex\Addresses\Helpers;

/**
 * Fluent builder for formatting a person's name in various display styles.
 *
 * Usage:
 *   (new NameGenerator('male', 'John', null, 'Doe', 'Dr', 'Jr'))
 *       ->withSalutation()
 *       ->withTitles()
 *       ->toString();
 *   // → "Mr. Dr John Doe, Jr"
 *
 * Shortcut for shipping labels:
 *   (new NameGenerator(...))->forShippingLabel()->toString();
 */
class NameGenerator
{
    /**
     * @param  string|null  $gender         Gender key used to look up salutation translations ('male', 'female', …)
     * @param  string|null  $first_name
     * @param  string|null  $middle_name
     * @param  string|null  $last_name
     * @param  string|null  $title_before   Honorific prefix, e.g. "Dr", "Prof"
     * @param  string|null  $title_after    Post-nominal suffix, e.g. "Jr", "Sr", "PhD"
     * @param  bool         $with_salutation       Prepend gendered salutation (e.g. "Mr.", "Ms.")
     * @param  bool         $with_titles           Wrap name with title_before / title_after
     * @param  bool         $with_care_of_prefix   Prepend the "c/o" translation from the addresses lang file
     * @param  bool         $with_name_reversed    Format as "Last, First" instead of "First Last"
     */
    public function __construct(
        protected ?string $gender,
        protected ?string $first_name,
        protected ?string $middle_name,
        protected ?string $last_name,
        protected ?string $title_before,
        protected ?string $title_after,
        protected bool $with_salutation = false,
        protected bool $with_titles = false,
        protected bool $with_care_of_prefix = false,
        protected bool $with_name_reversed = false,
    ) {}

    public function forShippingLabel(): self
    {
        return $this->withCareOfPrefix()->withSalutation()->withTitles();
    }

    public function withSalutation(): self
    {
        $this->with_salutation = true;

        return $this;
    }

    public function withTitles(): self
    {
        $this->with_titles = true;

        return $this;
    }

    public function withCareOfPrefix(): self
    {
        $this->with_care_of_prefix = true;

        return $this;
    }

    public function withNameReversed(): self
    {
        $this->with_name_reversed = true;

        return $this;
    }

    public function toString(): string
    {
        return trim(implode(' ', array_filter([
            $this->getCareOfPrefix(),
            $this->getSalutation(),
            $this->getNameWithTitles(),
        ])));
    }

    public function getCareOfPrefix(): string
    {
        if (!$this->with_care_of_prefix || !$this->last_name) {
            return '';
        }

        return trans('addresses::addresses.care-of');
    }

    public function getSalutation(): string
    {
        if (!$this->with_salutation || !$this->last_name) {
            return '';
        }

        return $this->gender ? trans('addresses::contacts.salutation.' . $this->gender) : '';
    }

    public function getName(): string
    {
        $first_names = trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
        ])));

        return $this->with_name_reversed ? trim(implode(', ', array_filter([$this->last_name, $first_names])))
                                         : trim(implode(' ', array_filter([$first_names, $this->last_name])));
    }

    public function getNameWithTitles(): string
    {
        if (!$this->with_titles) {
            return $this->getName();
        }

        $name = trim(implode(' ', array_filter([
            $this->title_before,
            $this->getName(),
        ])));

        return trim(implode(', ', array_filter([$name, $this->title_after])));
    }
}
