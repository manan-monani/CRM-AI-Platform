<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title inertia>{{ business_config('business_name', config('app.name', 'Laravel')) }}</title>

        @php
            $defaultFavicon = '/favicon.ico';
            $normalizeFaviconUrl = static function ($value) use ($defaultFavicon) {
                if (!is_string($value) || trim($value) === '') {
                    return $defaultFavicon;
                }

                $value = trim($value);
                if (
                    str_starts_with($value, 'http://')
                    || str_starts_with($value, 'https://')
                    || str_starts_with($value, '//')
                    || str_starts_with($value, 'data:')
                    || str_starts_with($value, '/')
                ) {
                    return $value;
                }

                if (str_starts_with($value, 'storage/')) {
                    return '/' . $value;
                }

                return '/storage/' . ltrim($value, '/');
            };

            $favicon = $normalizeFaviconUrl(business_config('favicon_url'));
            $faviconDarkValue = business_config('favicon_dark_url');
            $faviconDark = is_string($faviconDarkValue) && trim($faviconDarkValue) !== ''
                ? $normalizeFaviconUrl($faviconDarkValue)
                : $favicon;
        @endphp
        <link id="app-favicon" rel="icon" href="{{ $favicon }}">
        <link id="app-favicon-shortcut" rel="shortcut icon" href="{{ $favicon }}">
        <link id="app-favicon-apple" rel="apple-touch-icon" href="{{ $favicon }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <style>
            :root {
                /* Dynamic Colors */
                @foreach(config('branding.colors.css_vars', []) as $key => $data)
                {{ $key }}: {{ $data['key'] ? business_config($data['key'], $data['default']) : $data['default'] }};
                @endforeach

                --font-primary: "{{ business_config('font_primary', config('branding.fonts.primary', 'Instrument Sans')) }}", sans-serif;
                --font-secondary: "{{ business_config('font_secondary', config('branding.fonts.secondary', 'Roboto')) }}", sans-serif;
            }
        </style>

        <script>
            (function() {
                try {
                    const configDarkMode = {{ config('branding.theme.dark_mode', false) ? 'true' : 'false' }};
                    const persistedTheme = localStorage.getItem('theme');
                    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    const updateFavicon = (isDark) => {
                        const lightHref = @json($favicon);
                        const darkHref = @json($faviconDark);
                        const href = isDark ? darkHref : lightHref;
                        const favicon = document.getElementById('app-favicon');
                        const shortcutFavicon = document.getElementById('app-favicon-shortcut');
                        const appleFavicon = document.getElementById('app-favicon-apple');

                        if (favicon) {
                            favicon.href = href;
                        }

                        if (shortcutFavicon) {
                            shortcutFavicon.href = href;
                        }

                        if (appleFavicon) {
                            appleFavicon.href = href;
                        }
                    };

                    function setTheme(isDark) {
                         if (isDark) {
                            document.documentElement.classList.add('dark');
                        } else {
                            document.documentElement.classList.remove('dark');
                        }
                        updateFavicon(isDark);
                    }

                    if (persistedTheme === 'dark' || (!persistedTheme && (configDarkMode || systemDark))) {
                        setTheme(true);
                    } else {
                        setTheme(false);
                    }

                    // Observe class changes on html element for dynamic toggling
                    const observer = new MutationObserver((mutations) => {
                        mutations.forEach((mutation) => {
                            if (mutation.attributeName === 'class') {
                                const isDark = document.documentElement.classList.contains('dark');
                                updateFavicon(isDark);
                            }
                        });
                    });

                    observer.observe(document.documentElement, { attributes: true });
                } catch (e) {
                    console.error('Theme initialization failed', e);
                }
            })();
        </script>

        @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
