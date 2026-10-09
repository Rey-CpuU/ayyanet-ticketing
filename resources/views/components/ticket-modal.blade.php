{{-- Quick-view modal for a ticket (dashboard). Data comes from tickets.quick-details; every action is
     re-authorized server-side, the "can" flags only decide which controls are shown. --}}
<div x-data="ticketModalData(@js(url('tickets')))"
    x-show="open"
    x-cloak
    @open-ticket-modal.window="fetchTicketDetails($event.detail.ticketId)"
    @keydown.escape.window="if (showEditModal) { closeEditModal(); } else { closeModal(); }"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
    aria-modal="true"
    role="dialog">

    {{-- Backdrop --}}
    <div x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="if (!showEditModal) closeModal()"
        class="fixed inset-0 bg-black/75 backdrop-blur-sm"></div>

    {{-- Modal panel --}}
    <div x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        class="relative flex h-[88vh] max-h-[720px] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-[var(--border)] bg-[var(--surface)] text-[var(--foreground)] shadow-2xl">

        {{-- Loading overlay --}}
        <div x-show="loading" class="absolute inset-0 z-20 flex items-center justify-center bg-[var(--surface-90)] backdrop-blur-xs">
            <div class="flex items-center gap-3 rounded-full border border-[var(--border-strong)] bg-[var(--surface-2)] px-4 py-2 text-xs font-semibold text-[var(--accent)]">
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span>Memuat detail tiket...</span>
            </div>
        </div>

        <template x-if="loadError && !loading">
            <div class="flex flex-1 flex-col items-center justify-center gap-3 p-6 text-center">
                <p class="text-[13px] font-medium text-[var(--red-bright)]" x-text="loadError"></p>
                <button type="button" @click="closeModal()" class="btn-secondary">Tutup</button>
            </div>
        </template>

        <template x-if="ticket">
            <div class="flex flex-1 flex-col overflow-hidden">
                {{-- Header bar --}}
                <div class="border-b border-[var(--border)] bg-[var(--surface-2)] px-6 py-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="font-mono text-xs font-bold text-[var(--accent)]" x-text="ticket.ticket_number"></span>
                            <span class="badge" :class="statusBadgeClass(ticket.status)" x-text="ticket.status"></span>
                            <span class="flex items-center gap-1.5 font-mono text-[11px] font-bold uppercase tracking-wider" :class="'priority-' + priorityKey(ticket.priority)">
                                <span class="h-1.5 w-1.5 rounded-full" :class="'priority-dot-' + priorityKey(ticket.priority)"></span>
                                <span x-text="ticket.priority"></span>
                            </span>
                            <template x-if="ticket.sla">
                                <span class="rounded-full border px-2 py-[1px] font-mono text-[10px] font-semibold"
                                    :class="slaClass(ticket.sla.state)"
                                    :title="'SLA deadline ' + ticket.sla.deadline_label"
                                    x-text="'SLA ' + ticket.sla.label"></span>
                            </template>
                        </div>

                        <div class="flex items-center gap-2">
                            <template x-if="ticket.can.update">
                                <button type="button" @click="openEditModal()"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-[var(--border-strong)] bg-[var(--surface-3)] px-3 py-1.5 text-xs font-semibold text-[var(--foreground)] transition hover:border-[var(--accent)]">
                                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                        <path d="M11 2l3 3-9 9H2v-3l9-9z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span>Edit</span>
                                </button>
                            </template>
                            <a :href="ticket.show_url"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-[var(--green-text-30)] bg-[var(--green-text-10)] px-3 py-1.5 text-xs font-semibold text-[var(--green-text)] transition hover:bg-[var(--green-text-30)]">
                                <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                    <path d="M13.5 4.5L6.5 11.5L2.5 7.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span>Full View</span>
                            </a>
                            <button type="button" @click="closeModal()" aria-label="Tutup" class="rounded-lg p-1.5 text-[var(--muted)] transition hover:bg-[var(--surface-3)] hover:text-[var(--foreground)]">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                    <path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <h2 class="mt-2.5 text-base font-bold text-[var(--foreground)]" x-text="ticket.title"></h2>

                    {{-- Success notification --}}
                    <div x-show="editSuccessMessage"
                        x-cloak
                        x-transition
                        class="mt-3 flex items-center gap-2 rounded-lg border border-[var(--green-text-30)] bg-[var(--green-text-10)] px-3 py-2 text-xs font-semibold text-[var(--green-text)]">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span x-text="editSuccessMessage"></span>
                    </div>

                    {{-- Tabs --}}
                    <div class="mt-4 flex border-b border-[var(--border)]">
                        <button type="button" @click="activeTab = 'conversation'"
                            :class="activeTab === 'conversation' ? 'border-[var(--accent)] text-[var(--foreground)] font-bold' : 'border-transparent text-[var(--muted)] hover:text-[var(--foreground)]'"
                            class="border-b-2 px-4 py-2 text-xs font-semibold transition">
                            Conversation (<span x-text="ticket.messages ? ticket.messages.length : 0"></span>)
                        </button>
                        <button type="button" @click="activeTab = 'details'"
                            :class="activeTab === 'details' ? 'border-[var(--accent)] text-[var(--foreground)] font-bold' : 'border-transparent text-[var(--muted)] hover:text-[var(--foreground)]'"
                            class="border-b-2 px-4 py-2 text-xs font-semibold transition">
                            Details & Customer
                        </button>
                        <button type="button" @click="activeTab = 'activity'"
                            :class="activeTab === 'activity' ? 'border-[var(--accent)] text-[var(--foreground)] font-bold' : 'border-transparent text-[var(--muted)] hover:text-[var(--foreground)]'"
                            class="border-b-2 px-4 py-2 text-xs font-semibold transition">
                            Activity Log
                        </button>
                    </div>
                </div>

                {{-- Tab: conversation --}}
                <div x-show="activeTab === 'conversation'" class="flex flex-1 flex-col overflow-hidden">
                    <div id="modal-messages-container" class="ticket-detail-scroll flex-1 space-y-4 overflow-y-auto bg-[var(--background)] p-5">
                        <template x-if="!ticket.messages || ticket.messages.length === 0">
                            <div class="py-8 text-center text-xs text-[var(--muted)]">
                                Belum ada percakapan. Tulis balasan atau catatan internal di bawah.
                            </div>
                        </template>

                        <template x-for="msg in ticket.messages" :key="msg.id">
                            <div class="flex gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[var(--border-strong)] bg-[var(--surface-3)] text-[11px] font-bold text-[var(--foreground)]">
                                    <span x-text="msg.user_initials"></span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="mb-1 flex items-center gap-2">
                                        <span class="text-xs font-semibold text-[var(--foreground)]" x-text="msg.user_name"></span>
                                        <span class="text-[10px] text-[var(--muted)]" x-text="msg.date_str"></span>
                                        <template x-if="msg.is_internal">
                                            <span class="badge badge-amber px-1.5 py-0.5 text-[9px]">INTERNAL</span>
                                        </template>
                                    </div>
                                    <div :class="msg.is_internal ? 'border-[var(--amber-text-40)] bg-[var(--amber-text-07)]' : 'border-[var(--border)] bg-[var(--surface-2)]'"
                                        class="rounded-xl border p-3.5 text-xs leading-relaxed text-[var(--foreground)] shadow-xs">
                                        <p class="whitespace-pre-line" x-text="msg.message"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Composer --}}
                    <div class="border-t border-[var(--border)] bg-[var(--surface-2)] p-4" x-show="ticket.can.reply">
                        <div class="mb-2.5 flex items-center gap-2" x-show="ticket.can.internal_note">
                            <button type="button"
                                @click="isInternal = false"
                                :class="!isInternal ? 'bg-[var(--accent)] text-white shadow-xs' : 'bg-[var(--surface-3)] text-[var(--muted)] hover:text-[var(--foreground)]'"
                                class="rounded-lg px-3 py-1 text-xs font-semibold transition">
                                Balas Customer
                            </button>
                            <button type="button"
                                @click="isInternal = true"
                                :class="isInternal ? 'bg-[var(--amber-text)] text-black shadow-xs font-bold' : 'bg-[var(--surface-3)] text-[var(--muted)] hover:text-[var(--foreground)]'"
                                class="rounded-lg px-3 py-1 text-xs font-semibold transition">
                                Catatan Internal
                            </button>
                        </div>

                        <form @submit.prevent="sendMessage()" class="flex flex-col gap-2.5">
                            <textarea id="modal_new_message"
                                aria-label="Tulis balasan atau catatan internal"
                                x-model="newMessage"
                                rows="2"
                                maxlength="2000"
                                :placeholder="isInternal ? 'Tulis catatan internal untuk tim lapangan / CS...' : 'Tulis balasan untuk customer...'"
                                class="w-full rounded-xl border border-[var(--border-strong)] bg-[var(--surface)] p-3 text-xs text-[var(--foreground)] placeholder-[var(--muted)] focus:border-[var(--accent)] focus:outline-none focus:ring-1 focus:ring-[var(--accent)]"></textarea>

                            <div class="flex items-center justify-between gap-3">
                                <span class="text-[11px] text-[var(--muted)]" x-text="sendError || (isInternal ? 'Catatan ini hanya terlihat oleh tim internal (CS & teknisi).' : 'Pesan tercatat di riwayat tiket.')"></span>
                                <button type="submit"
                                    :disabled="sending || !newMessage.trim()"
                                    :class="isInternal ? 'bg-[var(--amber-text)] text-black font-bold' : 'bg-[var(--accent)] hover:bg-[var(--accent-hover)] text-white font-semibold'"
                                    class="inline-flex shrink-0 items-center gap-2 rounded-xl px-4 py-2 text-xs transition disabled:opacity-50">
                                    <span x-text="sending ? 'Mengirim...' : (isInternal ? 'Simpan Catatan' : 'Kirim Balasan')"></span>
                                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                        <path d="M2.5 8h11M9.5 3.5L14 8l-4.5 4.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Tab: details & customer --}}
                <div x-show="activeTab === 'details'" class="flex-1 space-y-6 overflow-y-auto bg-[var(--background)] p-6">
                    <div class="space-y-3 rounded-xl border border-[var(--border)] bg-[var(--surface-2)] p-4">
                        <div class="flex items-center justify-between border-b border-[var(--border)] pb-2.5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-[var(--accent)]">Informasi Customer</h3>
                            <span class="font-mono text-[11px] text-[var(--muted)]" x-text="'ID: ' + ticket.customer.customer_id"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-xs">
                            <div>
                                <span class="text-[var(--muted)]">Nama:</span>
                                <p class="mt-0.5 font-semibold text-[var(--foreground)]" x-text="ticket.customer.name"></p>
                            </div>
                            <div>
                                <span class="text-[var(--muted)]">Telepon / WhatsApp:</span>
                                <p class="mt-0.5 font-mono font-semibold text-[var(--green-text)]" x-text="ticket.customer.phone"></p>
                            </div>
                            <div class="col-span-2">
                                <span class="text-[var(--muted)]">Alamat Instalasi:</span>
                                <p class="mt-0.5 font-medium text-[var(--muted-strong)]" x-text="ticket.customer.address"></p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3 rounded-xl border border-[var(--border)] bg-[var(--surface-2)] p-4">
                        <div class="flex items-center justify-between border-b border-[var(--border)] pb-2.5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-[var(--accent)]">Detail Masalah</h3>
                            <span class="text-[11px] text-[var(--muted)]" x-text="'Dibuat: ' + ticket.created_human"></span>
                        </div>
                        <div class="space-y-3 text-xs">
                            <div>
                                <span class="text-[var(--muted)]">Deskripsi:</span>
                                <p class="mt-1 whitespace-pre-line rounded-lg border border-[var(--border)] bg-[var(--surface)] p-3 leading-relaxed text-[var(--foreground)]" x-text="ticket.description"></p>
                            </div>
                            <div class="grid grid-cols-2 gap-4 pt-1">
                                <div>
                                    <span class="text-[var(--muted)]">Kategori:</span>
                                    <p class="mt-0.5 font-semibold text-[var(--foreground)]" x-text="ticket.category || '-'"></p>
                                </div>
                                <div>
                                    <span class="text-[var(--muted)]">Penanggung Jawab:</span>
                                    <p class="mt-0.5 font-semibold text-[var(--foreground)]" x-text="ticket.assignee.name"></p>
                                </div>
                                <div>
                                    <span class="text-[var(--muted)]">OLT:</span>
                                    <p class="mt-0.5 font-mono font-medium text-[var(--amber-text)]" x-text="ticket.olt || '-'"></p>
                                </div>
                                <div>
                                    <span class="text-[var(--muted)]">Lokasi:</span>
                                    <p class="mt-0.5 font-mono font-medium text-[var(--foreground)]" x-text="ticket.location || '-'"></p>
                                </div>
                                <template x-if="ticket.sla">
                                    <div class="col-span-2">
                                        <span class="text-[var(--muted)]">SLA Deadline:</span>
                                        <p class="mt-0.5 font-mono font-semibold" :class="slaTextClass(ticket.sla.state)" x-text="ticket.sla.deadline_label + ' · ' + ticket.sla.label"></p>
                                    </div>
                                </template>
                                <template x-if="ticket.resolution_note">
                                    <div class="col-span-2">
                                        <span class="text-[var(--muted)]">Catatan Penyelesaian:</span>
                                        <p class="mt-0.5 whitespace-pre-line text-[var(--foreground)]" x-text="ticket.resolution_note"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab: activity --}}
                <div x-show="activeTab === 'activity'" class="ticket-detail-scroll flex-1 overflow-y-auto bg-[var(--background)]">
                    <template x-if="!ticket.activities || ticket.activities.length === 0">
                        <p class="px-5 py-10 text-center text-[12.5px] font-medium text-[var(--muted)]">Belum ada aktivitas tercatat.</p>
                    </template>
                    <template x-for="activity in ticket.activities" :key="activity.id">
                        <div class="flex items-start gap-3 border-b border-[var(--border-60)] px-5 py-3.5 last:border-0">
                            <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-[var(--border-strong)] bg-[var(--surface-3)] text-[10px] font-semibold text-[var(--foreground)]" x-text="activity.user_name.substring(0, 2).toUpperCase()"></div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="badge" :class="activityBadgeClass(activity.action)" x-text="activity.action_label"></span>
                                    <span class="text-[12.5px] font-semibold text-[var(--foreground)]" x-text="activity.user_name"></span>
                                    <span class="font-mono text-[10.5px] text-[var(--muted)]" x-text="activity.created_at"></span>
                                </div>
                                <template x-if="['status_change', 'priority_change', 'assignment'].includes(activity.action)">
                                    <p class="mt-1 text-[12px] text-[var(--muted)]">
                                        <span class="font-mono" x-text="activity.old_value || '-'"></span>
                                        <span class="mx-1 text-[var(--muted-strong)]">→</span>
                                        <span class="font-mono font-semibold text-[var(--foreground)]" x-text="activity.new_value || '-'"></span>
                                    </p>
                                </template>
                                <template x-if="!['status_change', 'priority_change', 'assignment'].includes(activity.action) && activity.new_value">
                                    <p class="mt-1 truncate text-[12px] text-[var(--muted)]" x-text="activity.new_value"></p>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>

    {{-- Nested edit popup --}}
    <div x-show="showEditModal"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6"
        aria-modal="true"
        role="dialog">

        <div @click="closeEditModal()" class="fixed inset-0 bg-black/80 backdrop-blur-sm"></div>

        <div x-show="showEditModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            @click.stop
            class="relative flex max-h-[90vh] w-full max-w-xl flex-col overflow-hidden rounded-2xl border border-[var(--border-strong)] bg-[var(--surface)] text-[var(--foreground)] shadow-2xl">

            <div class="flex items-center justify-between border-b border-[var(--border)] bg-[var(--surface-2)] px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--accent-soft)] text-[var(--accent)]">
                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <path d="M11 2l3 3-9 9H2v-3l9-9z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-[var(--foreground)]">Edit Ticket</h3>
                        <p class="font-mono text-[11px] text-[var(--accent)]" x-text="ticket ? ticket.ticket_number : ''"></p>
                    </div>
                </div>
                <button type="button" @click="closeEditModal()" aria-label="Tutup" class="rounded-lg p-1.5 text-[var(--muted)] transition hover:bg-[var(--surface-3)] hover:text-[var(--foreground)]">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                    </svg>
                </button>
            </div>

            <template x-if="editErrorMessage">
                <div class="border-b border-[var(--red-text-30)] bg-[var(--red-text-10)] px-6 py-2.5 text-xs font-semibold text-[var(--red-bright)]" x-text="editErrorMessage"></div>
            </template>

            <form @submit.prevent="saveTicketEdit()" class="flex flex-1 flex-col overflow-hidden">
                <div class="ticket-detail-scroll flex-1 space-y-4 overflow-y-auto p-6 text-xs">
                    <div>
                        <label for="edit_modal_ticket_title" class="label">Judul <span class="text-[var(--red-text)]">*</span></label>
                        <input type="text" id="edit_modal_ticket_title" x-model="editForm.title" required maxlength="255" class="input">
                    </div>

                    <div>
                        <label for="edit_modal_ticket_description" class="label">Deskripsi <span class="text-[var(--red-text)]">*</span></label>
                        <textarea id="edit_modal_ticket_description" x-model="editForm.description" rows="4" required class="input resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_modal_ticket_status" class="label">Status</label>
                            <select id="edit_modal_ticket_status" x-model="editForm.status" class="input">
                                <template x-for="st in statusOptions" :key="st">
                                    <option :value="st" x-text="st" :selected="st === editForm.status"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label for="edit_modal_ticket_category" class="label">Kategori</label>
                            <select id="edit_modal_ticket_category" x-model="editForm.category" class="input">
                                <template x-for="cat in (ticket ? ticket.categories : [])" :key="cat">
                                    <option :value="cat" x-text="cat" :selected="cat === editForm.category"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label for="edit_modal_ticket_priority" class="label">Prioritas</label>
                            <select id="edit_modal_ticket_priority" x-model="editForm.priority" class="input">
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>

                        <div>
                            <label for="edit_modal_ticket_olt" class="label">OLT</label>
                            <input type="text" id="edit_modal_ticket_olt" x-model="editForm.olt" maxlength="255" placeholder="mis. OLT-01" class="input">
                        </div>
                    </div>

                    <div x-show="['Solved', 'Closed'].includes(editForm.status) && editForm.status !== ticket?.status">
                        <label for="edit_modal_resolution_note" class="label">Catatan Penyelesaian</label>
                        <textarea id="edit_modal_resolution_note" x-model="editForm.resolution_note" rows="3" maxlength="2000"
                            placeholder="Wajib untuk Solved, atau Closed tanpa penyelesaian sebelumnya" class="input resize-none"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-[var(--border)] bg-[var(--surface-2)] px-6 py-3.5">
                    <button type="button" @click="closeEditModal()" class="btn-secondary">Batal</button>
                    <button type="submit" :disabled="savingEdit" class="btn-primary disabled:opacity-50">
                        <span x-text="savingEdit ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@once
<script>
    function ticketModalData(baseUrl) {
        const csrf = function () {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        };

        return {
            open: false,
            loading: false,
            loadError: '',
            activeTab: 'conversation',
            ticket: null,
            isInternal: false,
            newMessage: '',
            sending: false,
            sendError: '',

            showEditModal: false,
            savingEdit: false,
            editSuccessMessage: '',
            editErrorMessage: '',
            editForm: {},

            get statusOptions() {
                if (!this.ticket) return [];
                return [this.ticket.status, ...(this.ticket.allowed_statuses || [])];
            },

            async fetchTicketDetails(id) {
                this.open = true;
                this.loading = true;
                this.loadError = '';
                this.ticket = null;
                this.activeTab = 'conversation';
                this.newMessage = '';
                this.sendError = '';
                this.isInternal = false;
                this.showEditModal = false;
                this.editSuccessMessage = '';
                this.editErrorMessage = '';

                try {
                    const res = await fetch(baseUrl + '/' + id + '/quick-details', {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    this.ticket = await res.json();
                    this.$nextTick(() => this.scrollToBottom());
                } catch (err) {
                    this.loadError = 'Gagal memuat detail tiket.';
                } finally {
                    this.loading = false;
                }
            },

            closeModal() {
                this.open = false;
                this.showEditModal = false;
                this.ticket = null;
                this.loadError = '';
            },

            openEditModal() {
                if (!this.ticket || !this.ticket.can.update) return;
                this.editForm = {
                    customer_id: this.ticket.customer_id,
                    title: this.ticket.title || '',
                    description: this.ticket.description || '',
                    status: this.ticket.status,
                    category: this.ticket.category || '',
                    priority: this.ticket.priority || 'Medium',
                    olt: this.ticket.olt || '',
                    location: this.ticket.location || '',
                    resolution_note: '',
                };
                this.editErrorMessage = '';
                this.showEditModal = true;
            },

            closeEditModal() {
                this.showEditModal = false;
                this.editErrorMessage = '';
            },

            async saveTicketEdit() {
                if (!this.ticket || this.savingEdit) return;
                this.savingEdit = true;
                this.editErrorMessage = '';

                try {
                    const res = await fetch(this.ticket.update_url, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf(),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(this.editForm),
                    });
                    const data = await res.json().catch(() => ({}));

                    if (!res.ok) {
                        this.editErrorMessage = data.message || 'Gagal memperbarui tiket. Periksa kembali input Anda.';
                        return;
                    }

                    Object.assign(this.ticket, data.ticket || {});
                    this.ticket.allowed_statuses = data.allowed_statuses || this.ticket.allowed_statuses;
                    this.showEditModal = false;
                    this.editSuccessMessage = data.message || 'Tiket berhasil diperbarui';
                    setTimeout(() => { this.editSuccessMessage = ''; }, 3500);

                    window.dispatchEvent(new CustomEvent('ticket-updated', {
                        detail: {
                            id: this.ticket.id,
                            title: this.ticket.title,
                            status: this.ticket.status,
                            priority: this.ticket.priority,
                            category: this.ticket.category,
                        },
                    }));
                } catch (err) {
                    this.editErrorMessage = 'Terjadi kesalahan saat menyimpan perubahan.';
                } finally {
                    this.savingEdit = false;
                }
            },

            async sendMessage() {
                if (!this.newMessage.trim() || this.sending || !this.ticket) return;
                this.sending = true;
                this.sendError = '';

                try {
                    const res = await fetch(this.ticket.message_url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf(),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ message: this.newMessage, is_internal: this.isInternal ? 1 : 0 }),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (res.ok && data.success && data.message) {
                        this.ticket.messages.push(data.message);
                        this.newMessage = '';
                        this.$nextTick(() => this.scrollToBottom());
                    } else {
                        this.sendError = data.message || 'Pesan gagal dikirim.';
                    }
                } catch (err) {
                    this.sendError = 'Pesan gagal dikirim.';
                } finally {
                    this.sending = false;
                }
            },

            scrollToBottom() {
                const container = document.getElementById('modal-messages-container');
                if (container) container.scrollTop = container.scrollHeight;
            },

            statusBadgeClass(st) {
                return { 'Open': 'badge-red', 'Checking': 'badge-violet', 'Waiting Customer': 'badge-amber', 'Escalated': 'badge-orange', 'Solved': 'badge-green' }[st] || 'badge-slate';
            },
            priorityKey(prio) {
                return prio === 'High' ? 'high' : (prio === 'Medium' ? 'medium' : 'default');
            },
            slaClass(state) {
                return {
                    met: 'border-[var(--green-text-30)] bg-[var(--green-text-06)] text-[var(--green-text)]',
                    breached: 'border-[var(--red-text-40)] bg-[var(--red-solid-07)] text-[var(--red-bright)]',
                    paused: 'border-[var(--border-strong)] bg-[var(--surface-3)] text-[var(--muted)]',
                }[state] || 'border-[var(--amber-text-40)] bg-[var(--amber-text-07)] text-[var(--amber-text)]';
            },
            slaTextClass(state) {
                return { met: 'text-[var(--green-text)]', breached: 'text-[var(--red-bright)]', paused: 'text-[var(--muted)]' }[state] || 'text-[var(--amber-text)]';
            },
            activityBadgeClass(action) {
                return {
                    status_change: 'badge-blue',
                    priority_change: 'badge-blue',
                    internal_note: 'badge-amber',
                    assignment: 'badge-cyan',
                    sla_breached: 'badge-red',
                    created: 'badge-green',
                }[action] || 'badge-violet';
            },
        };
    }
</script>
@endonce
