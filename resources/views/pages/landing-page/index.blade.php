<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Prep4You — PrepCenter para Amazon FBA</title>
    <meta name="description" content="PrepCenter em São Bernardo do Campo: recebemos, conferimos, etiquetamos e enviamos para o FBA da Amazon com plataforma própria." />
    
    {{-- Font Poppins --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('css/landing-page.css') }}">
</head>

<body>
    @include('pages.landing-page.components.header')

    <main>
        @include('pages.landing-page.components.hero')
        @include('pages.landing-page.components.services')
        @include('pages.landing-page.components.how-it-works')
        @include('pages.landing-page.components.testimonials')
        @include('pages.landing-page.components.contact')
        @include('pages.landing-page.components.cta')
    </main>

    @include('pages.landing-page.components.footer')

    <script src="{{ asset('js/landing-page.js') }}"></script>
</body>
</html>
