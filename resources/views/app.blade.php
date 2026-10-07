<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="{{ $appSettings['seo_description'] ?? '' }}">
        <meta name="keywords" content="{{ $appSettings['seo_keywords'] ?? '' }}">

        <meta property="og:title" content="{{ $appSettings['seo_og_title'] ?? '' }}">
        <meta property="og:description" content="{{ $appSettings['seo_og_description'] ?? '' }}">
        <meta property="og:image" content="{{ ($appSettings['seo_og_image'] ?? '') ? asset('storage/' . $appSettings['seo_og_image']) : '' }}">

        <meta name="twitter:title" content="{{ $appSettings['seo_twitter_title'] ?? '' }}">
        <meta name="twitter:description" content="{{ $appSettings['seo_twitter_description'] ?? '' }}">
        <meta name="twitter:image" content="{{ ($appSettings['seo_twitter_image'] ?? '') ? asset('storage/' . $appSettings['seo_twitter_image']) : '' }}">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';
                const bg = '{{ $appSettings["site_base_color"] ?? "#852ba6" }}';
                const text = "#FFFFFF";

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
                document.documentElement.style.setProperty("--selection-bg", bg);
                document.documentElement.style.setProperty("--selection-text", text);
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
            :root {
                --base-color: {{ $appSettings['site_base_color'] ?? '#1656ad' }};
                --footer-bgcolor: {{ $appSettings['site_footer_bgcolor'] ?? '#334461' }};
                --footerbottom-bgcolor: {{ $appSettings['site_footer_bottom_bgcolor'] ?? '#343b54' }};
                --headertop-bgcolor: {{ $appSettings['site_header_top_bgcolor'] ?? '#343b54' }};
                --btn-base-color: {{ $appSettings['site_button_bgcolor'] ?? '#4b80c7' }};
                --btn-base-hover-color: {{ $appSettings['site_button_hover_bgcolor'] ?? '#061f40' }};
            }
        </style>

        <title inertia>{{ $appSettings['site_title'] ?? config('app.name') }}</title>


        <link rel="icon" href="{{ ($appSettings['favicon'] ?? '') ? asset('storage/' . $appSettings['favicon']) : asset('default/favicon.ico')  }}" type="image/x-icon"/>
        <link rel="icon" href="{{ asset('default/favicon.svg') }}" type="image/svg+xml">
        <link rel="apple-touch-icon" href="{{ asset('default/apple-touch-icon.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @routes
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/Pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
