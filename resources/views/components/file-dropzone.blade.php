@props([
    'name' => 'attachment',
    'id' => null,
    'label' => 'Lampiran',
    'accept' => \App\Models\Ticket::ATTACHMENT_MIMES,
    'maxKb' => \App\Models\Ticket::ATTACHMENT_MAX_KB,
    'currentName' => null, // existing file (edit form)
    'currentUrl' => null,  // download link for the existing file
])

@php
    $id = $id ?? $name;
    $extensions = array_values(array_map('strtolower', $accept));
    $acceptAttr = '.'.implode(',.', $extensions);
    $maxLabel = rtrim(rtrim(number_format($maxKb / 1024, 1, ',', '.'), '0'), ',').' MB';
    $serverErrors = $errors->get($name);
@endphp

<div
    x-data="fileDropzone({ accept: @js($extensions), maxKb: @js((int) $maxKb) })"
    x-on:paste.window="onPaste($event)"
    x-on:dragenter.prevent="onDragEnter()"
    x-on:dragover.prevent="$event.dataTransfer && ($event.dataTransfer.dropEffect = 'copy')"
    x-on:dragleave.prevent="onDragLeave()"
    x-on:drop.prevent="onDrop($event)"
    data-dropzone
    {{ $attributes->class('file-dropzone') }}
>
    <label for="{{ $id }}" class="label">{{ $label }}</label>

    @if ($currentName)
        <div class="mb-2 flex flex-wrap items-center gap-2.5 rounded-md border border-[var(--border)] bg-[var(--surface)] px-3 py-2.5" data-dropzone-current>
            <svg class="h-4 w-4 shrink-0 text-[var(--muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/>
            </svg>
            <div class="min-w-0 flex-1">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-[var(--muted)]">File saat ini</p>
                @if ($currentUrl)
                    <a href="{{ $currentUrl }}" class="block truncate font-mono text-[12.5px] text-[var(--dropzone-accent)] underline-offset-2 hover:underline" title="Unduh {{ $currentName }}">{{ $currentName }}</a>
                @else
                    <span class="block truncate font-mono text-[12.5px] text-[var(--foreground)]">{{ $currentName }}</span>
                @endif
                <p class="mt-0.5 text-[11.5px] text-[var(--muted)]" x-show="file" x-cloak>Akan diganti dengan file baru saat disimpan.</p>
            </div>
            <button type="button" class="btn-secondary !px-3 !py-1 text-[12px]" x-on:click="browse()" x-show="!file">Ganti file</button>
        </div>
    @endif

    <input
        type="file"
        name="{{ $name }}"
        id="{{ $id }}"
        accept="{{ $acceptAttr }}"
        x-ref="input"
        x-on:change="onInputChange()"
        class="file-dropzone-input sr-only"
        aria-describedby="{{ $id }}-hint {{ $id }}-error"
        x-bind:aria-invalid="(error !== '' || {{ $serverErrors ? 'true' : 'false' }}).toString()"
    >

    {{-- Drop area (also a label, so click / tap opens the native picker). --}}
    <label
        for="{{ $id }}"
        class="file-dropzone-area @if ($serverErrors) is-invalid @endif"
        x-show="!file"
        x-bind:class="{ 'is-dragging': dragging, 'is-invalid': error !== '' || {{ $serverErrors ? 'true' : 'false' }} }"
    >
        <span class="file-dropzone-icon" aria-hidden="true">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
            </svg>
        </span>
        <span class="text-[13px] font-semibold text-[var(--foreground)]">
            <span x-show="!dragging">Seret &amp; lepas file di sini, atau <span class="text-[var(--dropzone-accent)] underline underline-offset-2">pilih file</span></span>
            <span x-show="dragging" x-cloak>Lepaskan file untuk melampirkan</span>
        </span>
        <span id="{{ $id }}-hint" class="text-[11.5px] text-[var(--muted)]">
            Maks. {{ $maxLabel }}: {{ implode(', ', $extensions) }}.
            <span class="hidden sm:inline">Gambar juga bisa ditempel (Ctrl+V).</span>
        </span>
    </label>

    {{-- Selected file --}}
    <div class="file-dropzone-file" x-show="file" x-cloak x-bind:class="{ 'is-dragging': dragging }">
        <template x-if="previewUrl">
            <img x-bind:src="previewUrl" alt="" class="h-12 w-12 shrink-0 rounded-md border border-[var(--border)] object-cover">
        </template>
        <template x-if="!previewUrl">
            <span class="file-dropzone-type" x-bind:data-kind="fileKind" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/>
                    <path d="M14 2v6h6"/>
                </svg>
                <span class="file-dropzone-ext" x-text="fileExt"></span>
            </span>
        </template>

        <div class="min-w-0 flex-1">
            <p class="truncate text-[13px] font-semibold text-[var(--foreground)]" x-text="fileName" x-bind:title="fileName"></p>
            <p class="mt-0.5 text-[11.5px] text-[var(--muted)]">
                <span x-text="fileSize"></span>
                <span class="mx-1">·</span>
                <span class="uppercase" x-text="fileExt"></span>
            </p>
        </div>

        <button type="button" class="file-dropzone-btn hidden sm:inline-flex" x-on:click="browse()">Ganti</button>
        <button type="button" class="file-dropzone-btn file-dropzone-remove" x-on:click="remove()" aria-label="Hapus file terpilih" title="Hapus file">
            <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </button>
    </div>

    {{-- Screen-reader announcement of the current selection. --}}
    <p class="sr-only" aria-live="polite" x-text="file ? `File dipilih: ${fileName}, ${fileSize}` : ''"></p>

    <p id="{{ $id }}-error" class="mt-1.5 text-[12px] font-medium text-[var(--red-text)]" role="alert" aria-live="assertive" x-text="error" x-show="error !== ''" x-cloak></p>

    <x-input-error :messages="$serverErrors" class="mt-1.5" />
</div>
