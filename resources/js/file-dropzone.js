/**
 * Alpine component behind <x-file-dropzone>.
 *
 * The real <input type="file"> stays in the form, so a normal multipart submit
 * works. Dropped or pasted files are assigned to it via DataTransfer. The client
 * checks (extension + size) only give early feedback; the server stays authoritative.
 */
const ICON_BY_EXT = {
    jpg: 'image', jpeg: 'image', png: 'image', gif: 'image', webp: 'image',
    pdf: 'pdf',
    doc: 'doc', docx: 'doc',
    xls: 'sheet', xlsx: 'sheet', csv: 'sheet',
    txt: 'text',
};

export default function fileDropzone({ accept = [], maxKb = 5120 } = {}) {
    return {
        accept: accept.map((ext) => String(ext).toLowerCase()),
        maxBytes: maxKb * 1024,
        file: null,
        previewUrl: null,
        error: '',
        dragging: false,
        dragDepth: 0,

        get fileName() {
            return this.file ? this.file.name : '';
        },

        get fileExt() {
            return this.file ? this.extensionOf(this.file.name) : '';
        },

        get fileKind() {
            return ICON_BY_EXT[this.fileExt] || 'file';
        },

        get fileSize() {
            return this.file ? this.formatSize(this.file.size) : '';
        },

        extensionOf(name) {
            const dot = name.lastIndexOf('.');
            return dot === -1 ? '' : name.slice(dot + 1).toLowerCase();
        },

        formatSize(bytes) {
            if (bytes < 1024) return `${bytes} B`;
            if (bytes < 1024 * 1024) {
                return `${(bytes / 1024).toLocaleString('id-ID', { maximumFractionDigits: 1 })} KB`;
            }
            return `${(bytes / (1024 * 1024)).toLocaleString('id-ID', { maximumFractionDigits: 1 })} MB`;
        },

        validate(file) {
            const ext = this.extensionOf(file.name);
            if (!this.accept.includes(ext)) {
                return `Jenis file tidak didukung. Gunakan: ${this.accept.join(', ')}.`;
            }
            if (file.size > this.maxBytes) {
                // Round up so a file just over the limit never reads as "5 MB".
                const mb = Math.ceil((file.size / (1024 * 1024)) * 100) / 100;
                const shown = mb.toLocaleString('id-ID', { maximumFractionDigits: 2 });
                return `Ukuran file ${shown} MB melebihi batas maksimal ${this.formatSize(this.maxBytes)}.`;
            }
            if (file.size === 0) {
                return 'File kosong tidak dapat diunggah.';
            }
            return '';
        },

        onDragEnter() {
            this.dragDepth++;
            this.dragging = true;
        },

        onDragLeave() {
            this.dragDepth = Math.max(0, this.dragDepth - 1);
            if (this.dragDepth === 0) this.dragging = false;
        },

        onDrop(event) {
            this.dragDepth = 0;
            this.dragging = false;
            this.take(event.dataTransfer?.files);
        },

        onPaste(event) {
            const files = Array.from(event.clipboardData?.files || []);
            if (files.length === 0) return;
            event.preventDefault();
            const pasted = files[0];
            // Clipboard images are usually called "image.png"; give them a readable name.
            const ext = this.extensionOf(pasted.name) || (pasted.type.split('/')[1] || 'png');
            const stamp = new Date().toISOString().replace(/[-:T]/g, '').slice(0, 14);
            const named = new File([pasted], `tempelan-${stamp}.${ext}`, { type: pasted.type });
            this.take([named]);
        },

        /** Native picker selection (click / keyboard). */
        onInputChange() {
            const picked = this.$refs.input.files?.[0];
            if (!picked) {
                this.clear(false);
                return;
            }
            const message = this.validate(picked);
            if (message) {
                this.error = message;
                this.clear(true);
                return;
            }
            this.error = '';
            this.show(picked);
        },

        /** Dropped or pasted files: validate, then assign to the real input. */
        take(fileList) {
            const files = Array.from(fileList || []);
            if (files.length === 0) return;
            const picked = files[0];
            const message = this.validate(picked);
            if (message) {
                // Keep any previously valid selection untouched.
                this.error = message;
                return;
            }
            try {
                const transfer = new DataTransfer();
                transfer.items.add(picked);
                this.$refs.input.files = transfer.files;
            } catch (e) {
                this.error = 'Browser tidak mendukung seret & lepas. Klik area ini untuk memilih file.';
                return;
            }
            this.error = files.length > 1 ? 'Hanya satu file yang dapat dilampirkan; file pertama yang dipakai.' : '';
            this.show(picked);
        },

        show(file) {
            this.revokePreview();
            this.file = file;
            if (file.type.startsWith('image/')) {
                this.previewUrl = URL.createObjectURL(file);
            }
        },

        /** Remove the selected file (X button). */
        remove() {
            this.error = '';
            this.clear(true);
            this.$nextTick(() => this.$refs.input.focus());
        },

        clear(resetInput) {
            if (resetInput) this.$refs.input.value = '';
            this.revokePreview();
            this.file = null;
        },

        browse() {
            this.$refs.input.click();
        },

        revokePreview() {
            if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
            this.previewUrl = null;
        },

        destroy() {
            this.revokePreview();
        },
    };
}
