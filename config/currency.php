<?php

return [
  /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | This option controls the default currency that will be used by the
    | currency formatting and conversion features.
    |
    */

  'default' => env('DEFAULT_CURRENCY', 'NGN'),

  /*
    |--------------------------------------------------------------------------
    | Supported Currencies
    |--------------------------------------------------------------------------
    |
    | Here you may specify all of the currencies that should be supported
    | by the application. Each currency should have its ISO 4217 code.
    |
    */

  'supported' => [
    'NGN' => [
      'name' => 'Nigerian Naira',
      'symbol' => '₦',
      'symbol_position' => 'before',
      'decimal_separator' => '.',
      'thousand_separator' => ',',
      'decimals' => 2,
      'exchange_rate' => 1.00, // Base currency
    ],
    'GHS' => [
      'name' => 'Ghanaian Cedi',
      'symbol' => 'GH₵',
      'symbol_position' => 'before',
      'decimal_separator' => '.',
      'thousand_separator' => ',',
      'decimals' => 2,
      'exchange_rate' => 0.015, // 1 NGN = 0.015 GHS (example rate)
    ],
    'USD' => [
      'name' => 'US Dollar',
      'symbol' => '$',
      'symbol_position' => 'before',
      'decimal_separator' => '.',
      'thousand_separator' => ',',
      'decimals' => 2,
      'exchange_rate' => 0.0013, // 1 NGN = 0.0013 USD (example rate)
    ],
    'GBP' => [
      'name' => 'British Pound',
      'symbol' => '£',
      'symbol_position' => 'before',
      'decimal_separator' => '.',
      'thousand_separator' => ',',
      'decimals' => 2,
      'exchange_rate' => 0.0010, // 1 NGN = 0.0010 GBP (example rate)
    ],
    'CAD' => [
      'name' => 'Canadian Dollar',
      'symbol' => 'C$',
      'symbol_position' => 'before',
      'decimal_separator' => '.',
      'thousand_separator' => ',',
      'decimals' => 2,
      'exchange_rate' => 0.0018, // 1 NGN = 0.0018 CAD (example rate)
    ],
    'EUR' => [
      'name' => 'Euro',
      'symbol' => '€',
      'symbol_position' => 'after',
      'decimal_separator' => ',',
      'thousand_separator' => '.',
      'decimals' => 2,
      'exchange_rate' => 0.0012, // 1 NGN = 0.0012 EUR (example rate)
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | Exchange Rate API
    |--------------------------------------------------------------------------
    |
    | Configuration for external exchange rate API if you want to fetch
    | real-time exchange rates.
    |
    */

  'exchange_rate_api' => [
    'enabled' => env('EXCHANGE_RATE_API_ENABLED', false),
    'provider' => env('EXCHANGE_RATE_API_PROVIDER', 'exchangerate-api'),
    'key' => env('EXCHANGE_RATE_API_KEY'),
    'base_currency' => env('EXCHANGE_RATE_BASE_CURRENCY', 'NGN'),
  ],

  /*
    |--------------------------------------------------------------------------
    | Currency Session Key
    |--------------------------------------------------------------------------
    |
    | The session key used to store the user's selected currency.
    |
    */

  'session_key' => 'selected_currency',

  /*
    |--------------------------------------------------------------------------
    | Currency Cookie Settings
    |--------------------------------------------------------------------------
    |
    | Settings for the currency cookie that remembers user's preference.
    |
    */

  'cookie' => [
    'name' => 'selected_currency',
    'expire_minutes' => 60 * 24 * 30, // 30 days
  ],
];
