@props([
    'label',
    'value',
    'icon',
])

<div {{ $attributes->class('flex min-w-0 flex-col gap-1.5 rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900') }}>
    <div class="flex items-start justify-between gap-2">
        <flux:text class="text-sm leading-snug font-medium">{{ $label }}</flux:text>

        <div class="flex size-7 shrink-0 items-center justify-center rounded-md bg-primary/10 text-primary">
            <flux:icon :name="$icon" variant="micro" />
        </div>
    </div>

    <div class="text-2xl font-semibold tabular-nums text-zinc-900 dark:text-white">{{ $value }}</div>

    @isset($slot)
        <flux:text class="text-xs leading-snug">{{ $slot }}</flux:text>
    @endisset
</div>
