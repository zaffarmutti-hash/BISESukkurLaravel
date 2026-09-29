<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Board of Intermediate and Secondary Education, Sukkur — Official Student Enrollment and Examination Management System">
    <meta name="keywords" content="BISE Sukkur, Board of Education, SSC, HSC, Enrollment, Examination, Sindh">
    <meta name="author" content="BISE Sukkur">

    <title>{{ $title ?? config('app.name', 'BISE Sukkur') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="/images/bise-sukkur-logo.png?v={{ config('app.version', '1.0') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/bise-sukkur-logo.png?v={{ config('app.version', '1.0') }}">
    <link rel="shortcut icon" href="/images/bise-sukkur-logo.png?v={{ config('app.version', '1.0') }}">
    <link rel="apple-touch-icon" href="/images/bise-sukkur-logo.png?v={{ config('app.version', '1.0') }}">

    <!-- Google Fonts: Inter for UI, Noto Nastaliq Urdu for Urdu text -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Nastaliq+Urdu:wght@400;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-gray-50">
    @yield('content')
</body>
</html>
