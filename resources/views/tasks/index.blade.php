@php
    $linkWith = function (array $overrides) {
        $params = array_filter(
            array_merge(request()->only(['filter', 'q', 'sort']), $overrides),
            fn ($v) => $v !== null && $v !== ''
        );

        if (($params['filter'] ?? 'all') === 'all') {
            unset($params['filter']);
        }

        if (($params['sort'] ?? 'created') === 'created') {
            unset($params['sort']);
        }

        return url()->current() . ($params ? '?' . http_build_query($params) : '');
    };

    $filters = [
        'all' => ['label' => 'Semua', 'count' => $total],
        'active' => ['label' => 'Aktif', 'count' => $active],
        'completed' => ['label' => 'Selesai', 'count' => $completed],
    ];

    $sorts = [
        'created' => 'Terbaru',
        'due' => 'Jatuh tempo',
        'priority' => 'Prioritas',
        'title' => 'Judul A–Z',
    ];

    $now = now();
@endphp

<x-layout>
    <div x-data="editor()" x-on:edit-task.window="show($event.detail.task)">
        {{-- Header --}}
        <header class="flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="grid size-9 place-items-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-lg font-black text-white shadow-lg shadow-indigo-500/30">
                        ✓
                    </span>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            Tasks
                        </h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $now->translatedFormat('l, d F Y') }}
                        </p>
                    </div>
                </div>
            </div>

            <button
                type="button"
                x-data="theme()"
                x-init="init()"
                x-on:click="toggle()"
                class="grid size-10 place-items-center rounded-xl border border-slate-200 bg-white/80 text-slate-600 shadow-sm backdrop-blur transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-800 dark:bg-slate-900/70 dark:text-slate-300 dark:hover:border-indigo-700 dark:hover:text-indigo-300"
                :title="dark ? 'Mode terang' : 'Mode gelap'"
                aria-label="Ganti tema"
            >
                <svg x-show="!dark" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                    <circle cx="12" cy="12" r="4.2" />
                    <path d="M12 2.5v2M12 19.5v2M2.5 12h2M19.5 12h2M5.2 5.2l1.4 1.4M17.4 17.4l1.4 1.4M18.8 5.2l-1.4 1.4M6.6 17.4l-1.4 1.4" />
                </svg>
                <svg x-show="dark" x-cloak class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a8.5 8.5 0 1 0 10.5 10.5z" />
                </svg>
            </button>
        </header>

        {{-- Statistik + progres --}}
        <section class="mt-6 overflow-hidden rounded-3xl border border-slate-200/80 bg-white/80 p-5 shadow-sm backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/70">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                        Progres
                    </p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                        <span class="font-bold text-slate-900 dark:text-white">{{ $completed }}</span>
                        dari
                        <span class="font-bold text-slate-900 dark:text-white">{{ $total }}</span>
                        task selesai
                    </p>
                </div>
                <span class="bg-gradient-to-r from-indigo-500 to-violet-500 bg-clip-text text-4xl font-black tracking-tight text-transparent">
                    {{ $progress }}%
                </span>
            </div>

            <div class="mt-4 h-2.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                <div
                    class="h-full rounded-full bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500 transition-all duration-700 ease-out"
                    style="width: {{ $progress }}%"
                ></div>
            </div>

            <dl class="mt-5 grid grid-cols-3 divide-x divide-slate-200 text-center dark:divide-slate-800">
                <div class="px-2">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Aktif</dt>
                    <dd class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ $active }}</dd>
                </div>
                <div class="px-2">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Selesai</dt>
                    <dd class="mt-1 text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ $completed }}</dd>
                </div>
                <div class="px-2">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Terlambat</dt>
                    <dd class="mt-1 text-xl font-bold {{ $overdue > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400 dark:text-slate-600' }}">
                        {{ $overdue }}
                    </dd>
                </div>
            </dl>
        </section>

        {{-- Tambah task baru --}}
        <form
            action="{{ route('tasks.store') }}"
            method="POST"
            class="mt-5 rounded-3xl border border-slate-200/80 bg-white/80 p-4 shadow-sm backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/70"
            x-data="{ more: false }"
        >
            @csrf

            <div class="flex flex-col gap-2.5 sm:flex-row">
                <div class="relative flex-1">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <path d="M10 4.5v11M4.5 10h11" />
                    </svg>
                    <input
                        type="text"
                        name="title"
                        value="{{ old('title') }}"
                        placeholder="Apa yang harus dikerjakan?"
                        maxlength="255"
                        required
                        autofocus
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50/80 py-3 pl-10 pr-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white dark:focus:border-indigo-600 dark:focus:bg-slate-800"
                    >
                    @error('title')
                        <p class="mt-1.5 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="shrink-0 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/15 transition hover:bg-indigo-600 hover:shadow-indigo-600/25 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 active:scale-[0.98] dark:bg-white dark:text-slate-900 dark:hover:bg-indigo-500 dark:hover:text-white dark:focus-visible:ring-offset-slate-900"
                >
                    Tambah
                </button>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    x-on:click="more = !more"
                    class="inline-flex items-center gap-1.5 rounded-full border border-dashed border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-500 transition hover:border-indigo-400 hover:text-indigo-600 dark:border-slate-700 dark:text-slate-400 dark:hover:border-indigo-600 dark:hover:text-indigo-300"
                    x-bind:aria-expanded="more"
                >
                    <svg class="size-3.5 transition-transform duration-200" :class="{ 'rotate-45': more }" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M10 4.5v11M4.5 10h11" />
                    </svg>
                    <span x-text="more ? 'Sembunyikan detail' : 'Detail opsional'"></span>
                </button>

                <span class="text-[11px] text-slate-400 dark:text-slate-500">
                    Prioritas, jatuh tempo, dan catatan
                </span>
            </div>

            <div x-show="more" x-cloak x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="mt-3 grid gap-3 sm:grid-cols-2">
                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold text-slate-500 dark:text-slate-400">Prioritas</span>
                    <select
                        name="priority"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white"
                    >
                        @foreach (\App\Models\Task::PRIORITY_LABELS as $value => $label)
                            <option value="{{ $value }}" @selected(old('priority', 'medium') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold text-slate-500 dark:text-slate-400">Jatuh tempo</span>
                    <input
                        type="date"
                        name="due_date"
                        value="{{ old('due_date') }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white"
                    >
                </label>

                <label class="block sm:col-span-2">
                    <span class="mb-1.5 block text-xs font-semibold text-slate-500 dark:text-slate-400">Catatan</span>
                    <textarea
                        name="description"
                        rows="2"
                        maxlength="2000"
                        placeholder="Opsional…"
                        class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white"
                    >{{ old('description') }}</textarea>
                </label>
            </div>
        </form>

        {{-- Toolbar: pencarian + filter + urutan --}}
        <div class="mt-6 flex flex-col gap-3">
            <form
                method="GET"
                x-ref="searchForm"
                x-data="{ seed: @js($search) }"
                class="flex gap-2"
            >
                @if ($filter !== 'all')
                    <input type="hidden" name="filter" value="{{ $filter }}">
                @endif
                @if ($sort !== 'created')
                    <input type="hidden" name="sort" value="{{ $sort }}">
                @endif

                <div class="relative flex-1">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <circle cx="9" cy="9" r="5.5" />
                        <path d="m13.2 13.2 3.3 3.3" />
                    </svg>
                    <input
                        type="search"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Cari task…"
                        x-on:input.debounce.350ms="if ($event.target.value !== seed) { seed = $event.target.value; $refs.searchForm.submit(); }"
                        class="w-full rounded-xl border border-slate-200 bg-white/80 py-2.5 pl-10 pr-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-900/70 dark:text-white"
                    >
                </div>

                <label class="relative">
                    <span class="sr-only">Urutkan</span>
                    <select
                        name="sort"
                        x-on:change="$refs.searchForm.submit()"
                        class="h-full appearance-none rounded-xl border border-slate-200 bg-white/80 px-3 py-2.5 pr-8 text-sm font-medium text-slate-600 outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-300"
                    >
                        @foreach ($sorts as $value => $label)
                            <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <svg class="pointer-events-none absolute right-2.5 top-1/2 size-3.5 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6 8 4 4 4-4" />
                    </svg>
                </label>
            </form>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <nav class="inline-flex rounded-xl border border-slate-200 bg-white/80 p-1 shadow-sm backdrop-blur dark:border-slate-800 dark:bg-slate-900/70" aria-label="Filter task">
                    @foreach ($filters as $key => $meta)
                        <a
                            href="{{ $linkWith(['filter' => $key]) }}"
                            @class([
                                'rounded-lg px-3 py-1.5 text-xs font-semibold transition',
                                'bg-slate-900 text-white shadow dark:bg-white dark:text-slate-900' => $filter === $key,
                                'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' => $filter !== $key,
                            ])
                            @if ($filter === $key) aria-current="true" @endif
                        >
                            {{ $meta['label'] }}
                            <span class="ml-1 {{ $filter === $key ? 'opacity-70' : 'opacity-50' }}">{{ $meta['count'] }}</span>
                        </a>
                    @endforeach
                </nav>

                @if ($completed > 0)
                    <form action="{{ route('tasks.completed.clear') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 dark:text-slate-500 dark:hover:bg-rose-500/10 dark:hover:text-rose-300"
                        >
                            <svg class="size-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 6h12M8 6V4.5A1.5 1.5 0 0 1 9.5 3h1A1.5 1.5 0 0 1 12 4.5V6m3 0-.6 9a1.5 1.5 0 0 1-1.5 1.4H7.1a1.5 1.5 0 0 1-1.5-1.4L5 6" />
                            </svg>
                            Bersihkan yang selesai ({{ $completed }})
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Daftar task --}}
        <section class="mt-4">
            @if ($tasks->isEmpty())
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white/60 px-6 py-14 text-center backdrop-blur dark:border-slate-800 dark:bg-slate-900/50">
                    <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-gradient-to-br from-indigo-500/10 to-violet-500/10 text-2xl">
                        @if ($search !== '')
                            🔍
                        @elseif ($filter === 'completed')
                            🎉
                        @elseif ($filter === 'active')
                            🌱
                        @else
                            ☕
                        @endif
                    </div>

                    <h2 class="mt-4 text-base font-bold text-slate-800 dark:text-slate-100">
                        @if ($search !== '')
                            Tidak ada hasil untuk “{{ $search }}”
                        @elseif ($filter === 'completed')
                            Belum ada yang selesai
                        @elseif ($filter === 'active')
                            Semua task beres!
                        @else
                            Belum ada task
                        @endif
                    </h2>

                    <p class="mx-auto mt-1.5 max-w-sm text-sm text-slate-500 dark:text-slate-400">
                        @if ($search !== '')
                            Coba kata kunci lain atau hapus pencarian.
                        @elseif ($filter === 'completed')
                            Selesaikan satu task untuk melihatnya di sini.
                        @elseif ($filter === 'active')
                            Santai dulu, atau tambah pekerjaan baru di atas.
                        @else
                            Tulis hal pertama yang ingin kamu kerjakan hari ini.
                        @endif
                    </p>

                    @if ($search !== '' || $filter !== 'all')
                        <a
                            href="{{ $linkWith(['q' => '', 'filter' => 'all']) }}"
                            class="mt-5 inline-flex rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-600 dark:bg-white dark:text-slate-900 dark:hover:bg-indigo-500 dark:hover:text-white"
                        >
                            Tampilkan semua task
                        </a>
                    @endif
                </div>
            @else
                <ul class="flex flex-col gap-2.5">
                    @foreach ($tasks as $task)
                        <x-task-item :task="$task" :index="$loop->index" />
                    @endforeach
                </ul>

                <p class="mt-4 text-center text-xs text-slate-400 dark:text-slate-600">
                    Menampilkan {{ $tasks->count() }} dari {{ $total }} task
                    @if ($filter !== 'all')
                        · filter <span class="font-semibold">{{ $filters[$filter]['label'] }}</span>
                    @endif
                    @if ($search !== '')
                        · pencarian “{{ $search }}”
                    @endif
                </p>
            @endif
        </section>

        {{-- Modal edit --}}
        <div x-show="open" x-cloak class="fixed inset-0 z-40 flex items-end justify-center p-4 sm:items-center" role="dialog" aria-modal="true" aria-label="Edit task">
            <div
                x-show="open"
                x-transition:enter="transition duration-200 ease-out"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition duration-150 ease-in"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm"
                x-on:click="close()"
            ></div>

            <div
                x-show="open"
                x-transition:enter="transition duration-250 ease-out"
                x-transition:enter-start="translate-y-4 opacity-0 scale-95"
                x-transition:enter-end="translate-y-0 opacity-100 scale-100"
                x-transition:leave="transition duration-150 ease-in"
                x-transition:leave-start="translate-y-0 opacity-100 scale-100"
                x-transition:leave-end="translate-y-4 opacity-0 scale-95"
                x-on:keydown.escape.window="close()"
                class="relative z-10 max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Edit task</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Perbarui detail pekerjaanmu.</p>
                    </div>
                    <button
                        type="button"
                        x-on:click="close()"
                        class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300"
                        aria-label="Tutup"
                    >
                        <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="m5 5 10 10M15 5 5 15" />
                        </svg>
                    </button>
                </div>

                <form x-on:submit="submit($event)" class="mt-5 flex flex-col gap-4">
                    <label class="block">
                        <span class="mb-1.5 block text-xs font-semibold text-slate-500 dark:text-slate-400">Judul</span>
                        <input
                            type="text"
                            name="title"
                            x-ref="titleInput"
                            x-model="form.title"
                            maxlength="255"
                            required
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white"
                        >
                        <p x-show="errors.title" x-cloak class="mt-1.5 text-xs font-medium text-rose-600 dark:text-rose-400" x-text="errors.title?.[0]"></p>
                    </label>

                    <label class="block">
                        <span class="mb-1.5 block text-xs font-semibold text-slate-500 dark:text-slate-400">Catatan</span>
                        <textarea
                            name="description"
                            x-model="form.description"
                            rows="3"
                            maxlength="2000"
                            placeholder="Opsional…"
                            class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white"
                        ></textarea>
                        <p x-show="errors.description" x-cloak class="mt-1.5 text-xs font-medium text-rose-600 dark:text-rose-400" x-text="errors.description?.[0]"></p>
                    </label>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-semibold text-slate-500 dark:text-slate-400">Prioritas</span>
                            <select
                                name="priority"
                                x-model="form.priority"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white"
                            >
                                @foreach (\App\Models\Task::PRIORITY_LABELS as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="mb-1.5 block text-xs font-semibold text-slate-500 dark:text-slate-400">Jatuh tempo</span>
                            <input
                                type="date"
                                name="due_date"
                                x-model="form.due_date"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white"
                            >
                        </label>
                    </div>

                    <div class="mt-1 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            x-on:click="close()"
                            class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            x-bind:disabled="saving"
                            class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/15 transition hover:bg-indigo-600 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white dark:text-slate-900 dark:hover:bg-indigo-500 dark:hover:text-white"
                        >
                            <svg x-show="saving" x-cloak class="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25" />
                                <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                            </svg>
                            <span x-text="saving ? 'Menyimpan…' : 'Simpan perubahan'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
