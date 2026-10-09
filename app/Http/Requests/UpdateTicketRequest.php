<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket instanceof Ticket && ($this->user()?->can('update', $ticket) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['nullable', 'string', Rule::in(Ticket::PRIORITIES)],
            'status' => ['nullable', 'string', Rule::in(Ticket::STATUSES)],
            // Required when moving to Solved (or Closed without a prior resolution); see TicketWorkflow.
            'resolution_note' => ['nullable', 'string', 'max:2000'],
            // Tickets created by the Telegram bot (or older data) may carry a topic category
            // (Internet, Hardware, ...) outside the channel list; keeping it unchanged is allowed.
            'category' => ['nullable', 'string', Rule::in([...Ticket::CATEGORIES, $this->route('ticket')?->category])],
            'olt' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'attachment' => ['nullable', 'file', 'mimes:'.implode(',', Ticket::ATTACHMENT_MIMES), 'max:'.Ticket::ATTACHMENT_MAX_KB],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'attachment.mimes' => 'Lampiran harus berupa file: '.implode(', ', Ticket::ATTACHMENT_MIMES).'.',
            'attachment.max' => 'Ukuran lampiran maksimal 5 MB.',
        ];
    }
}
