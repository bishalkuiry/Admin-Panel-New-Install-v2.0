@php
    use App\Helpers\CurrencyHelper;
    $formatted = CurrencyHelper::format($amount, $currency ?? null);
@endphp
<span {{ $attributes }}>{{ $formatted }}</span>
