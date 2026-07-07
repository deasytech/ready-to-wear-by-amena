<?php

namespace App\Services;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class CurrencyService
{
    public function getSupportedCurrencies(): array
    {
        return config('currency.supported', []);
    }

    public function getCurrentCurrency(): string
    {
        $sessionKey = config('currency.session_key', 'selected_currency');
        $default = config('currency.default', 'NGN');

        $currency = Session::get($sessionKey) ?? Cookie::get(config('currency.cookie.name', $sessionKey)) ?? $default;

        return array_key_exists($currency, $this->getSupportedCurrencies()) ? $currency : $default;
    }

    public function setCurrentCurrency(string $currency): void
    {
        if (! array_key_exists($currency, $this->getSupportedCurrencies())) {
            return;
        }

        Session::put(config('currency.session_key', 'selected_currency'), $currency);

        Cookie::queue(
            config('currency.cookie.name', 'selected_currency'),
            $currency,
            config('currency.cookie.expire_minutes', 60 * 24 * 30)
        );
    }

    public function convert(float $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }

        $currencies = $this->getSupportedCurrencies();
        $fromRate = $currencies[$from]['exchange_rate'] ?? 1;
        $toRate = $currencies[$to]['exchange_rate'] ?? 1;

        // Normalise to base currency (NGN) first, then to the target currency.
        $baseAmount = $fromRate > 0 ? $amount / $fromRate : $amount;

        return round($baseAmount * $toRate, 2);
    }

    public function formatForDisplay(float|string|null $amount, ?string $currency = null): string
    {
        $currency = $currency ?? $this->getCurrentCurrency();
        $config = $this->getSupportedCurrencies()[$currency] ?? ['symbol' => '', 'symbol_position' => 'before', 'decimals' => 2, 'decimal_separator' => '.', 'thousand_separator' => ','];

        $formatted = number_format(
            (float) $amount,
            $config['decimals'] ?? 2,
            $config['decimal_separator'] ?? '.',
            $config['thousand_separator'] ?? ','
        );

        return $config['symbol_position'] === 'after'
            ? "{$formatted} {$config['symbol']}"
            : "{$config['symbol']}{$formatted}";
    }

    public function symbolFor(?string $currency = null): string
    {
        $currency = $currency ?? $this->getCurrentCurrency();

        return $this->getSupportedCurrencies()[$currency]['symbol'] ?? '';
    }
}
