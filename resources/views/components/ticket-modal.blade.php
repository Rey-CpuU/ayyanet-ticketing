<div x-data="ticketModalData()"
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

    {{-- Modal Panel --}}
    <div x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        class="relative flex h-[88vh] max-h-[720px] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-[var(--border)] bg-[#0d131a] text-[var(--foreground)] shadow-2xl">

        {{-- Loading Overlay --}}
        <div x-show="loading" class="absolute inset-0 z-20 flex items-center justify-center bg-[#0d131a]/80 backdrop-blur-xs">
            <div class="flex items-center gap-3 rounded-full border border-[var(--border-strong)] bg-[#121c24] px-4 py-2 text-xs font-semibold text-[var(--accent)]">
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span>Loading ticket details...</span>
            </div>
        </div>

        <template x-if="ticket">
            <div class="flex flex-1 flex-col overflow-hidden">
                {{-- Header Bar --}}
                <div class="border-b border-[var(--border)] bg-[#121c24] px-6 py-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="font-mono text-xs font-bold text-[var(--accent)]" x-text="ticket.ticket_number"></span>

                            {{-- Status Badge --}}
                            <span class="badge"
                                :class="{
                                      'badge-red': ticket.status === 'Open',
                                      'badge-violet': ticket.status === 'Checking',
                                      'badge-amber': ticket.status === 'Waiting Customer',
                                      'badge-orange': ticket.status === 'Escalated',
                                      'badge-green': ticket.status === 'Solved',
                                      'badge-slate': !['Open','Checking','Waiting Customer','Escalated','Solved'].includes(ticket.status)
                                  }"
                                x-text="ticket.status">
                            </span>

                            {{-- Priority Indicator --}}
                            <span class="flex items-center gap-1.5 font-mono text-[11px] font-bold uppercase tracking-wider"
                                :class="{
                                      'priority-high': ticket.priority === 'High',
                                      'priority-medium': ticket.priority === 'Medium',
                                      'priority-default': ticket.priority !== 'High' && ticket.priority !== 'Medium'
                                  }">
                                <span class="h-1.5 w-1.5 rounded-full"
                                    :class="{
                                          'priority-dot-high': ticket.priority === 'High',
                                          'priority-dot-medium': ticket.priority === 'Medium',
                                          'priority-dot-default': ticket.priority !== 'High' && ticket.priority !== 'Medium'
                                      }"></span>
                                <span x-text="ticket.priority"></span>
                            </span>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-2">
                            <button type="button" @click="openEditModal()"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-[var(--border-strong)] bg-[#192732] px-3 py-1.5 text-xs font-semibold text-[var(--foreground)] transition hover:bg-[#233d4d]">
                                <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                    <path d="M11 2l3 3-9 9H2v-3l9-9z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span>Edit</span>
                            </button>
                            <a :href="ticket.show_url"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-400 transition hover:bg-emerald-500/20">
                                <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                    <path d="M13.5 4.5L6.5 11.5L2.5 7.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span>Full View</span>
                            </a>
                            <button @click="closeModal()" class="rounded-lg p-1.5 text-[var(--muted)] transition hover:bg-[#192732] hover:text-[var(--foreground)]">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Title --}}
                    <h2 class="mt-2.5 text-base font-bold text-white" x-text="ticket.title"></h2>

                    {{-- Success Notification Banner --}}
                    <div x-show="editSuccessMessage"
                        x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="mt-3 flex items-center gap-2 rounded-lg border border-emerald-500/30 bg-emerald-500/15 px-3 py-2 text-xs font-semibold text-emerald-300">
                        <svg class="h-4 w-4 shrink-0 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span x-text="editSuccessMessage"></span>
                    </div>

                    {{-- Navigation Tabs --}}
                    <div class="mt-4 flex border-b border-[var(--border)]">
                        <button @click="activeTab = 'conversation'"
                            :class="activeTab === 'conversation' ? 'border-[var(--accent)] text-white font-bold' : 'border-transparent text-[var(--muted)] hover:text-white'"
                            class="border-b-2 px-4 py-2 text-xs font-semibold transition">
                            Conversation (<span x-text="ticket.messages ? ticket.messages.length : 0"></span>)
                        </button>
                        <button @click="activeTab = 'details'"
                            :class="activeTab === 'details' ? 'border-[var(--accent)] text-white font-bold' : 'border-transparent text-[var(--muted)] hover:text-white'"
                            class="border-b-2 px-4 py-2 text-xs font-semibold transition">
                            Details & Customer
                        </button>
                    </div>
                </div>

                {{-- Tab 1: Conversation --}}
                <div x-show="activeTab === 'conversation'" class="flex flex-1 flex-col overflow-hidden">
                    {{-- Messages List --}}
                    <div id="modal-messages-container" class="ticket-detail-scroll flex-1 space-y-4 overflow-y-auto p-5 bg-[#0a1015]">
                        <template x-if="!ticket.messages || ticket.messages.length === 0">
                            <div class="py-8 text-center text-xs text-[var(--muted)]">
                                Belum ada percakapan. Tulis balasan atau catatan internal di bawah.
                            </div>
                        </template>

                        <template x-for="msg in ticket.messages" :key="msg.id">
                            <div class="flex gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[var(--border-strong)] bg-[#192732] text-[11px] font-bold text-white">
                                    <span x-text="msg.user_initials"></span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="mb-1 flex items-center gap-2">
                                        <span class="text-xs font-semibold text-white" x-text="msg.user_name"></span>
                                        <span class="text-[10px] text-[var(--muted)]" x-text="msg.created_at"></span>
                                        <template x-if="msg.is_internal">
                                            <span class="badge badge-amber text-[9px] py-0.5 px-1.5">
                                                🔒 INTERNAL NOTE
                                            </span>
                                        </template>
                                    </div>
                                    <div :class="msg.is_internal ? 'bg-amber-500/10 border-amber-500/30 text-amber-200' : 'bg-[#121c24] border-[var(--border)] text-zinc-200'"
                                        class="rounded-xl border p-3.5 text-xs leading-relaxed shadow-xs">
                                        <p class="whitespace-pre-line" x-text="msg.message"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Composer Bar --}}
                    <div class="border-t border-[var(--border)] bg-[#121c24] p-4">
                        {{-- Mode Selector --}}
                        <div class="mb-2.5 flex items-center gap-2">
                            <button type="button"
                                @click="isInternal = false"
                                :class="!isInternal ? 'bg-[var(--accent)] text-white shadow-xs' : 'bg-[#192732] text-[var(--muted)] hover:text-white'"
                                class="rounded-lg px-3 py-1 text-xs font-semibold transition">
                                Reply Customer
                            </button>
                            <button type="button"
                                @click="isInternal = true"
                                :class="isInternal ? 'bg-amber-500 text-black shadow-xs font-bold' : 'bg-[#192732] text-[var(--muted)] hover:text-white'"
                                class="rounded-lg px-3 py-1 text-xs font-semibold transition">
                                🔒 Internal Note (Lap/CS)
                            </button>
                        </div>

                        <form @submit.prevent="sendMessage()" class="flex flex-col gap-2.5">
                            <textarea id="modal_new_message"
                                name="message"
                                aria-label="Tulis balasan atau catatan internal"
                                x-model="newMessage"
                                rows="2"
                                :placeholder="isInternal ? 'Tulis catatan internal untuk anak lapangan / CS...' : 'Tulis balasan untuk customer...'"
                                class="w-full rounded-xl border border-[var(--border-strong)] bg-[#0a1015] p-3 text-xs text-white placeholder-zinc-500 focus:border-[var(--accent)] focus:outline-none focus:ring-1 focus:ring-[var(--accent)]"></textarea>

                            <div class="flex items-center justify-between">
                                <span class="text-[11px] text-[var(--muted)]" x-text="isInternal ? 'Catatan ini hanya terlihat oleh tim internal CS & Teknisi.' : 'Pesan akan terkirim ke customer.'"></span>
                                <button type="submit"
                                    :disabled="sending || !newMessage.trim()"
                                    :class="isInternal ? 'bg-amber-500 hover:bg-amber-400 text-black font-bold' : 'bg-[var(--accent)] hover:bg-blue-600 text-white font-semibold'"
                                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs transition disabled:opacity-50">
                                    <span x-text="sending ? 'Sending...' : (isInternal ? 'Send Internal Note' : 'Send Reply')"></span>
                                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                        <path d="M2.5 8h11M9.5 3.5L14 8l-4.5 4.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Tab 2: Details & Customer --}}
                <div x-show="activeTab === 'details'" class="flex-1 overflow-y-auto bg-[#0a1015] p-6 space-y-6">
                    {{-- Customer Card --}}
                    <div class="rounded-xl border border-[var(--border)] bg-[#121c24] p-4 space-y-3">
                        <div class="flex items-center justify-between border-b border-[var(--border)] pb-2.5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-[var(--accent)]">Customer Information</h3>
                            <span class="font-mono text-[11px] text-[var(--muted)]" x-text="'ID: ' + (ticket.customer ? ticket.customer.customer_id : '-')"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-xs">
                            <div>
                                <span class="text-[var(--muted)]">Customer Name:</span>
                                <p class="mt-0.5 font-semibold text-white" x-text="ticket.customer ? ticket.customer.name : '-'"></p>
                            </div>
                            <div>
                                <span class="text-[var(--muted)]">Phone / WhatsApp:</span>
                                <p class="mt-0.5 font-semibold text-emerald-400 font-mono" x-text="ticket.customer ? ticket.customer.phone : '-'"></p>
                            </div>
                            <div class="col-span-2">
                                <span class="text-[var(--muted)]">Installation Address:</span>
                                <p class="mt-0.5 font-medium text-zinc-300" x-text="ticket.customer ? ticket.customer.address : '-'"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Technical & Problem Details --}}
                    <div class="rounded-xl border border-[var(--border)] bg-[#121c24] p-4 space-y-3">
                        <div class="flex items-center justify-between border-b border-[var(--border)] pb-2.5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-[var(--accent)]">Problem Specification</h3>
                            <span class="text-[11px] text-[var(--muted)]" x-text="'Created: ' + ticket.created_human"></span>
                        </div>
                        <div class="space-y-3 text-xs">
                            <div>
                                <span class="text-[var(--muted)]">Problem Description:</span>
                                <p class="mt-1 rounded-lg bg-[#0a1015] border border-[var(--border)] p-3 text-zinc-200 leading-relaxed whitespace-pre-line" x-text="ticket.description"></p>
                            </div>
                            <div class="grid grid-cols-2 gap-4 pt-1">
                                <div>
                                    <span class="text-[var(--muted)]">Category:</span>
                                    <p class="mt-0.5 font-semibold text-white" x-text="ticket.category || 'General'"></p>
                                </div>
                                <div>
                                    <span class="text-[var(--muted)]">Assigned Staff / Tech:</span>
                                    <p class="mt-0.5 font-semibold text-white" x-text="ticket.assignee ? ticket.assignee.name : 'Unassigned'"></p>
                                </div>
                                <div>
                                    <span class="text-[var(--muted)]">Device:</span>
                                    <p class="mt-0.5 font-mono font-medium text-amber-300" x-text="ticket.olt || '-'"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Nested Edit Ticket Popup Modal --}}
    <div x-show="showEditModal"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-60 flex items-center justify-center p-4 sm:p-6"
        aria-modal="true"
        role="dialog">

        {{-- Backdrop for Edit Modal --}}
        <div @click="closeEditModal()" class="fixed inset-0 bg-black/80 backdrop-blur-sm"></div>

        {{-- Edit Dialog Panel --}}
        <div x-show="showEditModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            @click.stop
            class="relative flex max-h-[90vh] w-full max-w-xl flex-col overflow-hidden rounded-2xl border border-[var(--border-strong)] bg-[#0d131a] text-[var(--foreground)] shadow-2xl">

            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-[var(--border)] bg-[#121c24] px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--accent-soft)] text-[var(--accent)]">
                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none">
                            <path d="M11 2l3 3-9 9H2v-3l9-9z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">Edit Ticket</h3>
                        <p class="font-mono text-[11px] text-[var(--accent)]" x-text="ticket ? ticket.ticket_number : ''"></p>
                    </div>
                </div>
                <button type="button" @click="closeEditModal()" class="rounded-lg p-1.5 text-[var(--muted)] transition hover:bg-[#192732] hover:text-white">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                    </svg>
                </button>
            </div>

            {{-- Error banner --}}
            <template x-if="editErrorMessage">
                <div class="border-b border-red-500/30 bg-red-500/10 px-6 py-2.5 text-xs font-semibold text-red-400" x-text="editErrorMessage"></div>
            </template>

            {{-- Form body --}}
            <form @submit.prevent="saveTicketEdit()" class="flex flex-1 flex-col overflow-hidden">
                <div class="ticket-detail-scroll flex-1 space-y-4 overflow-y-auto p-6 text-xs">
                    <div>
                        <label for="edit_modal_ticket_title" class="mb-1 block font-semibold text-[var(--muted)]">Title <span class="text-red-400">*</span></label>
                        <input type="text" id="edit_modal_ticket_title" name="title" x-model="editForm.title" required placeholder="Judul tiket..."
                            class="w-full rounded-xl border border-[var(--border-strong)] bg-[#0a1015] p-2.5 text-xs text-white placeholder-zinc-500 focus:border-[var(--accent)] focus:outline-none focus:ring-1 focus:ring-[var(--accent)]">
                    </div>

                    <div>
                        <label for="edit_modal_ticket_description" class="mb-1 block font-semibold text-[var(--muted)]">Description <span class="text-red-400">*</span></label>
                        <textarea id="edit_modal_ticket_description" name="description" x-model="editForm.description" rows="4" required placeholder="Deskripsi keluhan..."
                            class="w-full rounded-xl border border-[var(--border-strong)] bg-[#0a1015] p-2.5 text-xs text-white placeholder-zinc-500 focus:border-[var(--accent)] focus:outline-none focus:ring-1 focus:ring-[var(--accent)] resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_modal_ticket_status" class="mb-1 block font-semibold text-[var(--muted)]">Status <span class="text-red-400">*</span></label>
                            <select id="edit_modal_ticket_status" name="status" x-model="editForm.status"
                                class="w-full rounded-xl border border-[var(--border-strong)] bg-[#0a1015] p-2.5 text-xs text-white focus:border-[var(--accent)] focus:outline-none focus:ring-1 focus:ring-[var(--accent)]">
                                <option value="Open">Open</option>
                                <option value="Checking">Checking</option>
                                <option value="Waiting Customer">Waiting Customer</option>
                                <option value="Escalated">Escalated</option>
                                <option value="Solved">Solved</option>
                                <option value="Closed">Closed</option>
                            </select>
                        </div>

                        <div>
                            <label for="edit_modal_ticket_category" class="mb-1 block font-semibold text-[var(--muted)]">Category</label>
                            <select id="edit_modal_ticket_category" name="category" x-model="editForm.category"
                                class="w-full rounded-xl border border-[var(--border-strong)] bg-[#0a1015] p-2.5 text-xs text-white focus:border-[var(--accent)] focus:outline-none focus:ring-1 focus:ring-[var(--accent)]">
                                <option value="">— Select —</option>
                                <option value="Internet">Internet</option>
                                <option value="Hardware">Hardware</option>
                                <option value="Billing">Billing</option>
                                <option value="Layanan">Layanan</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label for="edit_modal_ticket_priority" class="mb-1 block font-semibold text-[var(--muted)]">Priority <span class="text-red-400">*</span></label>
                            <select id="edit_modal_ticket_priority" name="priority" x-model="editForm.priority"
                                class="w-full rounded-xl border border-[var(--border-strong)] bg-[#0a1015] p-2.5 text-xs text-white focus:border-[var(--accent)] focus:outline-none focus:ring-1 focus:ring-[var(--accent)]">
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>

                        <div>
                            <label for="edit_modal_ticket_olt" class="mb-1 block font-semibold text-[var(--muted)]">Device</label>
                            <input type="text" id="edit_modal_ticket_olt" name="olt" x-model="editForm.olt" placeholder="e.g. OLT-01"
                                class="w-full rounded-xl border border-[var(--border-strong)] bg-[#0a1015] p-2.5 text-xs text-white placeholder-zinc-500 focus:border-[var(--accent)] focus:outline-none focus:ring-1 focus:ring-[var(--accent)]">
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 border-t border-[var(--border)] bg-[#121c24] px-6 py-3.5">
                    <button type="button" @click="closeEditModal()" class="rounded-xl border border-[var(--border-strong)] bg-[#192732] px-4 py-2 text-xs font-semibold text-zinc-300 transition hover:bg-[#233d4d] hover:text-white">
                        Cancel
                    </button>
                    <button type="submit" :disabled="savingEdit" class="inline-flex items-center gap-2 rounded-xl bg-[var(--accent)] px-4 py-2 text-xs font-semibold text-white transition hover:bg-blue-600 disabled:opacity-50">
                        <span x-text="savingEdit ? 'Saving...' : 'Save Changes'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function ticketModalData() {
        return {
            open: false,
            loading: false,
            activeTab: 'conversation',
            ticketId: null,
            ticket: null,
            isInternal: false,
            newMessage: '',
            sending: false,

            // Edit Modal State
            showEditModal: false,
            savingEdit: false,
            editSuccessMessage: '',
            editErrorMessage: '',
            editForm: {
                title: '',
                description: '',
                status: 'Open',
                category: '',
                priority: 'Medium',
                olt: '',
                location: ''
            },

            fetchTicketDetails(id) {
                this.ticketId = id;
                this.open = true;
                this.loading = true;
                this.activeTab = 'conversation';
                this.newMessage = '';
                this.showEditModal = false;
                this.editSuccessMessage = '';
                this.editErrorMessage = '';

                fetch(`/tickets/${id}/quick-details`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.ticket = data;
                        this.loading = false;
                        this.$nextTick(() => this.scrollToBottom());
                    })
                    .catch(err => {
                        console.error('Failed to load ticket details:', err);
                        this.loading = false;
                    });
            },

            closeModal() {
                this.open = false;
                this.showEditModal = false;
                this.ticket = null;
            },

            openEditModal() {
                if (!this.ticket) return;
                this.editForm = {
                    title: this.ticket.title || '',
                    description: this.ticket.description || '',
                    status: this.ticket.status || 'Open',
                    category: this.ticket.category || '',
                    priority: this.ticket.priority || 'Medium',
                    olt: this.ticket.olt || '',
                    location: this.ticket.location || ''
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

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                try {
                    const res = await fetch(`/tickets/${this.ticket.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(this.editForm)
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        this.editErrorMessage = data.message || 'Gagal memperbarui tiket. Silakan periksa input Anda.';
                        this.savingEdit = false;
                        return;
                    }

                    // Update current ticket state in Alpine
                    this.ticket.title = this.editForm.title;
                    this.ticket.description = this.editForm.description;
                    this.ticket.status = this.editForm.status;
                    this.ticket.category = this.editForm.category;
                    this.ticket.priority = this.editForm.priority;
                    this.ticket.olt = this.editForm.olt;
                    this.ticket.location = this.editForm.location;

                    this.showEditModal = false;
                    this.editSuccessMessage = 'Tiket berhasil diperbarui';
                    setTimeout(() => {
                        this.editSuccessMessage = '';
                    }, 3500);

                    // Dispatch global event for live updates
                    window.dispatchEvent(new CustomEvent('ticket-updated', { 
                        detail: { 
                            id: this.ticket.id, 
                            title: this.ticket.title, 
                            status: this.ticket.status,
                            priority: this.ticket.priority,
                            category: this.ticket.category
                        } 
                    }));

                } catch (err) {
                    console.error('Error updating ticket:', err);
                    this.editErrorMessage = 'Terjadi kesalahan saat menyimpan perubahan.';
                } finally {
                    this.savingEdit = false;
                }
            },

            sendMessage() {
                if (!this.newMessage.trim() || this.sending) return;

                this.sending = true;

                const payload = {
                    message: this.newMessage,
                    is_internal: this.isInternal ? 1 : 0
                };

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                fetch(`/tickets/${this.ticket.id}/quick-message`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success && res.message) {
                            if (!this.ticket.messages) this.ticket.messages = [];
                            this.ticket.messages.push(res.message);
                            this.newMessage = '';
                            this.$nextTick(() => this.scrollToBottom());
                        }
                        this.sending = false;
                    })
                    .catch(err => {
                        console.error('Failed to send message:', err);
                        this.sending = false;
                    });
            },

            scrollToBottom() {
                const container = document.getElementById('modal-messages-container');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            }
        };
    }
</script>