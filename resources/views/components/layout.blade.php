<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Tasks' }} · Todo App</title>

    {{-- Terapkan dark mode sebelum paint supaya tidak berkedip. --}}
    <script>
        (function () {
            var dark = localStorage.getItem('theme') === 'dark' ||
                (!localStorage.getItem('theme') && matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen font-sans selection:bg-indigo-500/20" x-data="{ }">
    {{-- Latar: gradasi lembut + pola titik, menutupi seluruh viewport. --}}
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden bg-dots">
        <div class="absolute -top-40 left-1/2 h-[32rem] w-[32rem] -translate-x-1/2 rounded-full bg-indigo-400/25 blur-3xl dark:bg-indigo-600/20"></div>
        <div class="absolute -bottom-32 -left-24 h-96 w-96 rounded-full bg-sky-400/20 blur-3xl dark:bg-sky-600/15"></div>
        <div class="absolute -right-24 top-1/3 h-80 w-80 rounded-full bg-fuchsia-400/20 blur-3xl dark:bg-fuchsia-600/15"></div>
    </div>

    {{-- Pesan flash dari session, dibaca Alpine untuk menampilkan toast. --}}
    @if (session('status'))
        <div
            hidden
            data-flash-message="{{ session('status') }}"
            data-flash-type="{{ session('status_type', 'success') }}"
        ></div>
    @endif

    @if ($errors->any())
        <div
            hidden
            data-flash-message="{{ $errors->first() }}"
            data-flash-type="error"
        ></div>
    @endif

    <div class="mx-auto w-full max-w-3xl px-4 pb-24 pt-10 sm:px-6 sm:pt-14">
        {{ $slot }}
    </div>

    {{-- Toast --}}
    <div
        x-data="toast()"
        x-init="init()"
        x-show="show"
        x-transition:enter="transition duration-300 ease-out"
        x-transition:enter-start="translate-y-3 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition duration-200 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-100 translate-y-3"
        class="pointer-events-none fixed inset-x-0 bottom-6 z-50 flex justify-center px-4"
        role="status"
        aria-live="polite"
    >
        <div
            class="pointer-events-auto flex items-center gap-3 rounded-2xl border px-4 py-3 text-sm font-medium shadow-xl backdrop-blur-xl"
            :class="type === 'error'
                ? 'border-rose-200 bg-rose-50/90 text-rose-700 dark:border-rose-900 dark:bg-rose-950/90 dark:text-rose-300'
                : 'border-emerald-200 bg-emerald-50/90 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/90 dark:text-emerald-300'"
        >
            <span
                class="grid size-5 shrink-0 place-items-center rounded-full text-xs"
                :class="type === 'error' ? 'bg-rose-500 text-white' : 'bg-emerald-500 text-white'"
                aria-hidden="true"
            >
                <span x-show="type !== 'error'">✓</span>
                <span x-show="type === 'error'">!</span>
            </span>
            <span x-text="message"></span>
        </div>
    </div>
</body>
</html>
