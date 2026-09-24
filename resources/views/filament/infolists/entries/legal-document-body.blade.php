@php
    $record = $getRecord();
@endphp
{{-- Собственный <style>-блок вместо утилит Tailwind/Filament (.fi-prose): гарантирует
     отступы между абзацами/списками/заголовками независимо от того, что именно
     Filament решит собрать в свою CSS-сборку. --}}
<style>
    .legal-doc-admin-body { font-size: 14px; line-height: 1.7; color: rgb(24 24 27); }
    .legal-doc-admin-body p { margin: 0 0 16px 0; }
    .legal-doc-admin-body p:last-child { margin-bottom: 0; }
    .legal-doc-admin-body h1, .legal-doc-admin-body h2, .legal-doc-admin-body h3 { margin: 24px 0 12px 0; font-weight: 700; line-height: 1.3; }
    .legal-doc-admin-body h1:first-child, .legal-doc-admin-body h2:first-child, .legal-doc-admin-body h3:first-child { margin-top: 0; }
    .legal-doc-admin-body ul, .legal-doc-admin-body ol { margin: 0 0 16px 0; padding-left: 24px; }
    .legal-doc-admin-body li { margin-bottom: 6px; }
    .legal-doc-admin-body a { color: rgb(243 115 56); text-decoration: underline; }
    .legal-doc-admin-body strong { font-weight: 700; }
    .legal-doc-admin-body blockquote { margin: 0 0 16px 0; padding-left: 16px; border-left: 3px solid rgb(228 228 231); color: rgb(113 113 122); }
</style>
<div class="legal-doc-admin-body">
    {!! $record->body !!}
</div>
