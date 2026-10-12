<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    /** Fields a field technician (lapangan) may change; everything else is ignored for them. */
    public const FIELD_TECHNICIAN_FIELDS = ['status', 'resolution_note', 'olt', 'location'];

    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket instanceof Ticket && ($this->user()?->can('update', $ticket) ?? false);
    }

    /**
     * Whether the current user may edit the core fields (see TicketPolicy::updateDetails).
     */
    public function canEditDetails(): bool
    {
        return (bool) $this->user()?->can('updateDetails', $this->route('ticket'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $ticket = $this->route('ticket');

        $rules = [
            // Soft-deleted customers cannot be picked, but a ticket may keep the one it already has.
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')->where(
                fn ($query) => $query->whereNull('deleted_at')->orWhere('id', $ticket?->customer_id)
            )],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['nullable', 'string', Rule::in(Ticket::PRIORITIES)],
            'status' => ['nullable', 'string', Rule::in(Ticket::STATUSES)],
            // Required when moving to Solved (or Closed without a prior resolution); see TicketWorkflow.
            'resolution_note' => ['nullable', 'string', 'max:2000'],
            // Older tickets (e.g. from the former Telegram bot) may carry a topic category
            // (Internet, Hardware, ...) outside the channel list; keeping it unchanged is allowed.
            'category' => ['nullable', 'string', Rule::in([...Ticket::CATEGORIES, $ticket?->category])],
            'olt' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'attachment' => ['nullable', 'file', 'mimes:'.implode(',', Ticket::ATTACHMENT_MIMES), 'max:'.Ticket::ATTACHMENT_MAX_KB],
        ];

        // Field technicians: only the allowed fields are validated (and thus end up in validated());
        // title, customer, priority, category, description and attachment are ignored.
        return $this->canEditDetails()
            ? $rules
            : array_intersect_key($rules, array_flip(self::FIELD_TECHNICIAN_FIELDS));
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_id.exists' => 'Customer tidak ditemukan atau sudah dihapus.',
            'attachment.mimes' => 'Lampiran harus berupa file: '.implode(', ', Ticket::ATTACHMENT_MIMES).'.',
            'attachment.max' => 'Ukuran lampiran maksimal 5 MB.',
        ];
    }
}
