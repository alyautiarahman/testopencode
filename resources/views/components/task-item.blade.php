@props(['task', 'index' => 0])

@php
    $dueLabel = null;
    $dueTone = '';

    if ($task->due_date) {
        $diff = (int) today()->diffInDays($task->due_date->toDateString(), false);

        if ($task->isOverdue()) {
            $dueLabel = 'Terlambat ' . abs($diff) . ' hr';
            $dueTone = 'text-rose-600 dark:text-rose-400';
        } elseif ($task->isDueToday()) {
            $dueLabel = 'Hari ini';
            $dueTone = 'text-amber-600 dark:text-amber-400';
        } elseif ($diff === 1) {
            $dueLabel = 'Besok';
            $dueTone = 'text-slate-500 dark:text-slate-400';
        } else {
            $dueLabel = $task->due_date->isoFormat('D MMM');
            $dueTone = 'text-slate-500 dark:text-slate-400';
        }
    }

    $priorityColors = [
        'high' => 'bg-rose-500',
        'medium' => 'bg-amber-500',
        'low' => 'bg-sky-500',
    ];
    $priorityColor = $priorityColors[$task->priority] ?? $priorityColors['medium'];
@endphp

<li
    x-data="{ confirming: false }"
    x-init="$watch('confirming', v => { if (!v) $el.querySelector('form.delete-form')?.reset(); })"
    {{ $attributes->merge(['class' => 'group relative flex animate-fade-up gap-3 rounded-2xl border border-slate-200/80 bg-white/80 p-3.5 shadow-sm backdrop-blur-sm transition duration-200 hover:border-indigo-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900/70 dark:hover:border-indigo-700']) }}
    style="animation-delay: {{ $index * 35 }}ms"
    data-task-id="{{ $task->id }}"
>
    {{-- Strip prioritas di sisi kiri --}}
    <span class="absolute inset-y-3 left-0 w-1 rounded-full {{ $priorityColor }}" aria-hidden="true"></span>

    {{-- Toggle selesai --}}
    <form action="{{ route('tasks.toggle', $task) }}" method="POST" class="shrink-0 pt-0.5 pl-1.5">
        @csrf
        <button
            type="submit"
            class="relative grid size-6 place-items-center rounded-full border-2 transition duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900 {{ $task->is_completed ? 'border-emerald-500 bg-emerald-500' : 'border-slate-300 hover:border-indigo-500 dark:border-slate-600 dark:hover:border-indigo-400' }}"
            title="{{ $task->is_completed ? 'Buka kembali' : 'Tandai selesai' }}"
            aria-pressed="{{ $task->is_completed ? 'true' : 'false' }}"
        >
            <svg
                class="size-3.5 {{ $task->is_completed ? 'text-white animate-pop' : 'text-white' }}"
                viewBox="0 0 20 20"
                fill="none"
                stroke="currentColor"
                stroke-width="3"
                stroke-linecap="round"
                stroke-linejoin="round"
                {{ $task->is_completed ? '' : 'hidden' }}
            >
                <path d="M4 10.5 8 14.5 16 6" />
            </svg>
        </button>
    </form>

    {{-- Konten --}}
    <div class="min-w-0 flex-1 pl-1">
        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
            <h3
                class="truncate text-sm font-semibold {{ $task->is_completed ? 'text-slate-400 line-through dark:text-slate-600' : 'text-slate-800 dark:text-slate-100' }}"
            >
                {{ $task->title }}
            </h3>
            <x-priority-badge :priority="$task->priority" size="sm" />
        </div>

        @if ($task->description)
            <p class="mt-1 line-clamp-2 text-xs leading-relaxed {{ $task->is_completed ? 'text-slate-400 dark:text-slate-600' : 'text-slate-500 dark:text-slate-400' }}">
                {{ $task->description }}
            </p>
        @endif

        @if ($dueLabel)
            <p class="mt-1.5 flex items-center gap-1 text-[11px] font-medium {{ $dueTone }}">
                <svg class="size-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="10" cy="10" r="7.5" />
                    <path d="M10 6v4.2l2.6 1.6" stroke-linecap="round" />
                </svg>
                {{ $dueLabel }}
                @if ($task->isOverdue())
                    <span class="rounded-full bg-rose-100 px-1.5 py-px text-[10px] font-bold uppercase tracking-wide dark:bg-rose-500/15">
                        Overdue
                    </span>
                @endif
            </p>
        @endif
    </div>

    {{-- Aksi --}}
    <div class="flex shrink-0 items-start gap-1 opacity-100 transition duration-200 sm:opacity-0 sm:group-hover:opacity-100 sm:group-focus-within:opacity-100">
        <button
            type="button"
            x-on:click="$dispatch('edit-task', { task: {
                id: {{ $task->id }},
                title: @js($task->title),
                description: @js($task->description),
                priority: @js($task->priority),
                due_date: @js($task->due_date?->format('Y-m-d')),
            } })"
            class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-300"
            title="Edit task"
            aria-label="Edit task"
        >
            <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M13.6 3.9a1.7 1.7 0 0 1 2.4 2.4L7.5 14.8l-3.2.8.8-3.2z" />
            </svg>
        </button>

        <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="delete-form">
            @csrf
            @method('DELETE')

            <button
                type="button"
                x-show="!confirming"
                x-on:click="confirming = true"
                class="grid size-8 place-items-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10 dark:hover:text-rose-300"
                title="Hapus task"
                aria-label="Hapus task"
            >
                <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 6h12M8 6V4.5A1.5 1.5 0 0 1 9.5 3h1A1.5 1.5 0 0 1 12 4.5V6m3 0-.6 9a1.5 1.5 0 0 1-1.5 1.4H7.1a1.5 1.5 0 0 1-1.5-1.4L5 6" />
                </svg>
            </button>

            <div x-show="confirming" x-cloak class="flex items-center gap-1" x-transition>
                <span class="text-[11px] font-semibold text-rose-500">Hapus?</span>
                <button
                    type="submit"
                    class="rounded-lg bg-rose-500 px-2 py-1 text-[11px] font-bold text-white transition hover:bg-rose-600"
                >
                    Ya
                </button>
                <button
                    type="button"
                    x-on:click="confirming = false"
                    class="rounded-lg bg-slate-200 px-2 py-1 text-[11px] font-bold text-slate-600 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600"
                >
                    Batal
                </button>
            </div>
        </form>
    </div>
</li>
