@props(['priority' => 'medium', 'size' => 'md'])

@php
    $colors = [
        'high' => 'bg-rose-100 text-rose-700 ring-rose-200 dark:bg-rose-500/15 dark:text-rose-300 dark:ring-rose-500/30',
        'medium' => 'bg-amber-100 text-amber-700 ring-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-500/30',
        'low' => 'bg-sky-100 text-sky-700 ring-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:ring-sky-500/30',
    ];

    $label = \App\Models\Task::PRIORITY_LABELS[$priority] ?? 'Medium';
    $class = $colors[$priority] ?? $colors['medium'];
    $padding = $size === 'sm' ? 'px-1.5 py-0.5 text-[10px]' : 'px-2 py-0.5 text-xs';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 rounded-full font-semibold uppercase tracking-wide ring-1 ring-inset {$padding} {$class}"]) }}>
    {{ $label }}
</span>
