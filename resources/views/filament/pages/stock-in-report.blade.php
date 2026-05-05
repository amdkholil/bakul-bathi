<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit.prevent="">
            {{ $this->form }}
        </form>

        <div class="grid gap-6 md:grid-cols-3">
            @foreach ($this->getStats() as $stat)
                <div class="fi-ta-ctn overflow-hidden border border-gray-200 shadow-sm rounded-xl dark:border-white/10 dark:bg-gray-900 bg-white p-6">
                    {{-- Row 1: Label --}}
                    <div class="flex items-center gap-x-2">
                        @if ($stat['icon'])
                            <x-filament::icon
                                :icon="$stat['icon']"
                                class="h-4 w-4 text-gray-400 dark:text-gray-500"
                            />
                        @endif
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ $stat['label'] }}
                        </span>
                    </div>

                    {{-- Row 2: Value --}}
                    <div class="mt-1 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
                        {{ $stat['value'] }}
                    </div>

                    {{-- Row 3: Description --}}
                    @if ($stat['description'])
                        <div @class([
                            'mt-1 text-xs font-medium',
                            'text-success-600 dark:text-success-400' => ($stat['color'] ?? '') === 'success',
                            'text-primary-600 dark:text-primary-400' => ($stat['color'] ?? '') === 'primary',
                            'text-warning-600 dark:text-warning-400' => ($stat['color'] ?? '') === 'warning',
                            'text-gray-600 dark:text-gray-400' => ($stat['color'] ?? '') === 'gray' || !($stat['color'] ?? null),
                        ])>
                            {{ $stat['description'] }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
