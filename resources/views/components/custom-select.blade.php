@props([
    'name',
    'id' => null,
    'value' => '',
    'options' => [], // array of ['value' => '...', 'label' => '...'] or ['value' => 'label']
    'placeholder' => 'Select...',
    'submitOnChange' => false,
    'width' => 'w-full',
    'buttonClass' => ''
])

@php
$id = $id ?? $name;
// Normalize options into list of [value, label]
$normalizedOptions = [];
foreach ($options as $k => $v) {
    if (is_array($v)) {
        $normalizedOptions[] = ['value' => (string)($v['value'] ?? $k), 'label' => (string)($v['label'] ?? $v['value'] ?? $k)];
    } else {
        $normalizedOptions[] = ['value' => (string)$k, 'label' => (string)$v];
    }
}

// Find initial label
$selectedLabel = $placeholder;
foreach ($normalizedOptions as $opt) {
    if ((string)$opt['value'] === (string)$value && (string)$opt['value'] !== '') {
        $selectedLabel = $opt['label'];
        break;
    }
}
@endphp

<div class="relative inline-block w-full sm:w-auto"
     :class="{ 'z-50': open, 'z-10': !open }"
     x-data="{
         open: false,
         selectedValue: '{{ $value }}',
         selectedLabel: '{{ $selectedLabel }}',
         selectOption(val, lbl) {
             this.selectedValue = val;
             this.selectedLabel = lbl;
             this.open = false;
             $nextTick(() => {
                 const hiddenInput = $refs.hiddenInput;
                 if (hiddenInput) {
                     hiddenInput.value = val;
                     @if($submitOnChange)
                     hiddenInput.closest('form')?.submit();
                     @else
                     hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                     @endif
                 }
             });
         }
     }"
     @click.outside="open = false"
     @keydown.escape.stop="open = false">

    {{-- Hidden Form Input --}}
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" x-ref="hiddenInput" :value="selectedValue" value="{{ $value }}">

    {{-- Dropdown Trigger Button --}}
    <button type="button"
            @click="open = !open"
            class="flex w-full items-center justify-between gap-3 rounded-lg border border-[var(--border-strong)] bg-[var(--surface)] px-3.5 py-2.5 text-[13px] font-medium text-[var(--foreground)] transition-all duration-200 hover:border-[var(--accent)] hover:bg-[var(--surface-3)] focus:border-[var(--accent)] focus:outline-none min-h-[38px] {{ $buttonClass }}">
        <span class="truncate" x-text="selectedLabel">{{ $selectedLabel }}</span>
        <svg class="h-3.5 w-3.5 shrink-0 text-[var(--muted)] transition-transform duration-200"
             :class="{ 'rotate-180 text-[var(--accent)]': open }"
             viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </button>

    {{-- Custom Styled Animated Dropdown Menu --}}
    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
         class="absolute left-0 top-full z-50 mt-1.5 {{ $width }} min-w-[160px] overflow-hidden rounded-xl border border-[var(--border-strong)] bg-[var(--surface-2)] shadow-2xl shadow-black/50 backdrop-blur-md">
        
        <div class="py-1 max-h-60 overflow-y-auto divide-y divide-[var(--border-60)]">
            @foreach($normalizedOptions as $opt)
            <button type="button"
                    @click="selectOption('{{ $opt['value'] }}', '{{ $opt['label'] }}')"
                    class="group flex w-full items-center justify-between px-3.5 py-2.5 text-left text-[12.5px] transition-all duration-150 hover:bg-[var(--hover-overlay)] hover:pl-4.5"
                    :class="selectedValue === '{{ $opt['value'] }}' ? 'font-semibold text-[var(--accent)] bg-[var(--surface-3)]' : 'font-medium text-[var(--foreground)]'">
                <span class="truncate">{{ $opt['label'] }}</span>
                <span x-show="selectedValue === '{{ $opt['value'] }}'" class="text-[var(--accent)] shrink-0">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </span>
            </button>
            @endforeach
        </div>
    </div>
</div>
