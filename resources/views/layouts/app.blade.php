<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ITY Miniapp')</title>
    <link rel="stylesheet" href="/css/app.css">
    <script type="module" src="/js/app.js"></script>
</head>
{{--
x-data on

<body> is required. It makes Alpine's $store magic
    property available in every child element on the page.
    --}}

    <body x-data>
        @yield('content')
    </body>

</html>