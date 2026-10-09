<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Ticket::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')->whereNull('deleted_at')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['nullable', 'string', Rule::in(Ticket::PRIORITIES)],
            // Optional manual override; detected by App\Support\TicketClassifier when empty.
            'impact' => ['nullable', 'string', Rule::in(Ticket::IMPACTS)],
            // New tickets always start as Open; other states are reached through updates.
            'status' => ['nullable', 'string', Rule::in(['Open'])],
            'category' => ['nullable', 'string', Rule::in(Ticket::CATEGORIES)],
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
            'status.in' => 'Ticket baru harus berstatus Open.',
            'attachment.mimes' => 'Lampiran harus berupa file: '.implode(', ', Ticket::ATTACHMENT_MIMES).'.',
            'attachment.max' => 'Ukuran lampiran maksimal 5 MB.',
        ];
    }
}
