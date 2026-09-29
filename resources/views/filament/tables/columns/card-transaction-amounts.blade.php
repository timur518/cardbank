@php
    $currency = $record->currency;
@endphp
<div class="leading-tight">
    <div class="font-semibold">{{ \Illuminate\Support\Number::currency((float) $record->amount, $currency) }}</div>
    @if ($record->cost_amount !== null)
        <div class="text-xs text-gray-500">{{ \Illuminate\Support\Number::currency((float) $record->cost_amount, $currency) }}</div>
    @endif
    @if ($record->commission_amount !== null)
        <div class="text-xs text-gray-500">{{ \Illuminate\Support\Number::currency((float) $record->commission_amount, $currency) }}</div>
    @endif
</div>
