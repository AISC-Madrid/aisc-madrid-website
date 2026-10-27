@props([
    'label',
    'value',
    'icon',
])

<div {{ $attributes->class('flex flex-col gap-3 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900') }}>
    <div class="flex items-center justify-between gap-3">
        <flux:text class="text-sm font-medium">{{ $label }}</flux:text>

        <div class="flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
            <flux:icon :name="$icon" variant="mini" />
        </div>
    </div>

    <div class="text-3xl font-semibold tabular-nums text-zinc-900 dark:text-white">{{ $value }}</div>

    @isset($slot)
        <flux:text class="text-sm">{{ $slot }}</flux:text>
    @endisset
</div>
