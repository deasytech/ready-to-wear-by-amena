<?php

/*
 * For more details about the configuration, see:
 * https://sweetalert2.github.io/#configuration
 */

return [
    // Position::Center's value, spelled out as a plain string rather than
    // referencing the enum: this file is required during Laravel's config
    // bootstrap on every single request, before the framework has even
    // started routing - if the jantinnerezo/livewire-alert package is ever
    // missing/mismatched in vendor/ (e.g. a deploy where composer install
    // wasn't run), referencing the enum class here would fatal the entire
    // site rather than just the (currently unused) alert feature.
    'position' => 'center',
    'timer' => 3000,
    'toast' => false,
    'text' => null,
    'confirmButtonText' => 'Yes',
    'cancelButtonText' => 'Cancel',
    'denyButtonText' => 'No',
    'showCancelButton' => false,
    'showConfirmButton' => false,
    'backdrop' => true,
];
