@if ($available)
    <span class="orange-button-label{{ ($mobile ?? false) ? ' orange-button-label-mobile' : '' }}">
        <span class="orange-button-caption"><span class="orange-button-title">{{ $ctaLabel }}</span> <small class="orange-button-subtitle">онлайн за 3 минуты</small></span>
        @if ($mobile ?? false)<span class="orange-button-mobile-arrow" aria-hidden="true">↗︎</span>@endif
    </span>
@else
    {{ $ctaLabel }}@if ($mobile ?? false) ↗︎@endif
@endif
