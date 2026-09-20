@php
    $color = $record->color ?: '#111827';
@endphp
@if ($record->logo_svg)
    <div style="width:36px;height:36px;border-radius:10px;background:{{ $color }};display:flex;align-items:center;justify-content:center">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="#fff">{!! $record->logo_svg !!}</svg>
    </div>
@else
    <div style="width:36px;height:36px;border-radius:10px;background:#e5e7eb"></div>
@endif
