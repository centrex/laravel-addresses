<?php

return [
    'load_migrations' => env('ADDRESSES_LOAD_MIGRATIONS', true),

    /*
     * Addresses
     */
    'addresses' => [
        /*
         * Main table.
         */
        'table' => 'addresses',

        /*
         * The model used for addresses.
         */
        'model' => \Centrex\Addresses\Models\Address::class,

        /*
         * Flag columns to be added to table.
         */
        'flags' => ['public', 'primary', 'billing', 'shipping'],

        /*
         * The validation rules for an address.
         */
        'rules' => [
            'type'         => 'nullable|string|max:40',
            'street'       => 'required|string|min:3|max:180',
            'street_extra' => 'nullable|string|max:180',
            'city'         => 'required|string|min:2|max:120',
            'state'        => 'nullable|string|max:120',
            'district'     => 'nullable|string|max:120',
            'region'       => 'nullable|string|max:120',
            'post_code'    => 'required|string|max:30',
            'country_id'   => 'required|integer',
            'country_code' => 'nullable|string|size:2',
        ],

        /*
         * Enable automatic geocoding to add coordinates (lon/lat) to addresses.
         * If you enable this option, please make sure to also add a
         * Google Maps API Key to your services' config file.
         * The key used is 'services.google.maps.key'.
         * Default: false
         */
        'geocode' => false,
    ],

    /*
     * Contacts
     */
    'contacts' => [
        /*
         * Main table.
         */
        'table' => 'contacts',

        /*
         * The model used for contacts.
         */
        'model' => \Centrex\Addresses\Models\Contact::class,

        /*
         * Flag columns to be added to table.
         */
        'flags' => ['public', 'primary'],

        /*
         * The validation rules for a contact.
         */
        'rules' => [],
    ],
];
