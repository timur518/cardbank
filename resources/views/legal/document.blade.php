<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — Mojno</title>
    <link rel="icon" href="/assets/images/favicon.svg" sizes="32x32" type="image/png">
    <link rel="icon" href="/assets/images/favicon.svg" sizes="16x16" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sofia-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">

@include('partials.site-header')

<main class="bg-[#f3f0ee]">
    <section class="px-6 pb-24 pt-[160px] lg:px-10">
        <div class="mx-auto max-w-[860px]">
            <h1 class="text-3xl font-extrabold tracking-tight text-[#3A3C40] lg:text-4xl">{{ $title }}</h1>

            @if ($document)
                <p class="mt-3 text-sm text-[#3A3C40]/50">
                    Версия {{ $document->version }}
                    @if ($document->effective_at)
                        · вступает в силу {{ $document->effective_at->format('d.m.Y') }}
                    @endif
                </p>

                <div class="legal-doc-content mt-10">
                    {!! $document->body !!}
                </div>
            @else
                <p class="mt-6 text-base leading-relaxed text-[#3A3C40]/60">
                    Документ пока не опубликован. Загляните позже.
                </p>
            @endif
        </div>
    </section>
</main>

@include('partials.site-footer')
</body>
</html>
