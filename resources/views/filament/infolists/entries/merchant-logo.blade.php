@php
    $record = $getRecord();
    $color = $record->color ?: '#111827';
@endphp
@if ($record->logo_svg)
    <div style="width:56px;height:56px;border-radius:14px;background:{{ $color }};display:flex;align-items:center;justify-content:center">
        <svg viewBox="0 0 24 24" width="30" height="30" fill="#fff">{!! $record->logo_svg !!}</svg>
    </div>
@else
    <div style="width:56px;height:56px;border-radius:14px;background:#e5e7eb"></div>
@endif
