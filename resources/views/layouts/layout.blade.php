<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
    @include('layouts.head')
    <script>
        @if(session('dark_mode'))
            // Define the CSS to be injected
            var darkModeCSS = `
            <style id="darkModeStyle">
                html {
                color: white !important;
                background-color: #262626 !important;
                }
                .popupheader, header, footer, .ticket, iframe {
                background-color: #6e6e6e !important;
                }
                input[type=text], input[type=number], input[type=date], input[type=datetime-local], select, textarea {
                background-color: #6e6e6e !important;
                color: white !important;
                }
            </style>
            `;

            // Check if dark mode style already exists, if not, add it
            if (!$('#darkModeStyle').length) {
                $('head').append(darkModeCSS);
            }
        @endif

        function toggleDarkMode(){
            window.location.href = "/toggleDarkMode";
        }

    </script>
    </head>
    <body>
        <header>
            @include('layouts.header')
        </header>
        <main>
            @yield('content')
        </main>
        @include('layouts.footer')
    </body>
</html>