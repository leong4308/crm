<!DOCTYPE html>

<html class="{{ request()->cookie('dark_mode') ? 'dark' : '' }}" lang="{{ app()->getLocale() }}"
    dir="{{ in_array(app()->getLocale(), ['fa', 'ar']) ? 'rtl' : 'ltr' }}">

<head>

    {!! view_render_event('admin.layout.head.before') !!}

    <title>{{ $title }}</title>

    <meta charset="UTF-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="content-language" content="{{ app()->getLocale() }}">

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="base-url" content="{{ url()->to('/') }}">
    <meta name="currency" content="{{
    json_encode([
        'code' => config('app.currency'),
        'symbol' => core()->currencySymbol(config('app.currency'))
    ])
            }}
        ">

    @stack('meta')

    {{
    vite()->set(['src/Resources/assets/css/app.css', 'src/Resources/assets/js/app.js'])
    }}


    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="preload" as="image" href="{{ url('cache/logo/bagisto.png') }}">

    @if ($favicon = core()->getConfigData('general.general.admin_logo.favicon_image'))
        <link type="image/x-icon" href="{{ Storage::url($favicon) }}" rel="shortcut icon" sizes="16x16">
    @else
        {{-- SVG favicon: modern browsers prefer this and it renders crisp at all sizes --}}
        <link type="image/svg+xml" href="{{ vite()->asset('images/logo.svg') }}" rel="icon" />
    @endif


    @php
        $brandColor = core()->getConfigData('general.settings.menu_color.brand_color') ?? '#8DA399';
    @endphp

    @stack('styles')

    <style>
        :root {
            --brand-color:
                {{ $brandColor }}
            ;
        }

        {!! core()->getConfigData('general.content.custom_scripts.custom_css') !!}
    </style>

    {!! view_render_event('admin.layout.head.after') !!}
</head>

<body class="h-full font-jakarta dark:bg-gray-950">
    {!! view_render_event('admin.layout.body.before') !!}

    <div id="app" class="h-full">
        <!-- Flash Message Blade Component -->
        <x-admin::flash-group />

        <!-- Confirm Modal Blade Component -->
        <x-admin::modal.confirm />

        {!! view_render_event('admin.layout.content.before') !!}

        <!-- Page Header Blade Component -->
        <x-admin::layouts.header />

        <div class="group/container sidebar-collapsed flex gap-4" ref="appLayout">
            <!-- Page Sidebar Blade Component -->
            <x-admin::layouts.sidebar.desktop />

            <div
                class="flex min-h-[calc(100vh-62px)] max-w-full flex-1 flex-col bg-gray-100 pt-3 transition-all duration-300 dark:bg-gray-950">
                <!-- Page Content Blade Component -->
                <div class="px-4 pb-[72px] ltr:lg:pl-[85px] rtl:lg:pr-[85px]">
                    {{ $slot }}
                </div>


            </div>
        </div>

        {!! view_render_event('admin.layout.content.after') !!}
    </div>

    {!! view_render_event('admin.layout.body.after') !!}

    @stack('scripts')

    {!! view_render_event('admin.layout.vue-app-mount.before') !!}

    <script>
        /**
         * Cargar evento, el propósito de usar el evento es montar la aplicación
         * después de que todos nuestros componentes `Vue` que están presentes en el archivo Blade tengan
         * sido registrado en la aplicación. No importa cuál debería ser `app.mount()`
         * llamado en el último.
         */
        window.addEventListener("load", function (event) {
            app.mount("#app");
        });
    </script>

    {!! view_render_event('admin.layout.vue-app-mount.after') !!}
</body>

</html>