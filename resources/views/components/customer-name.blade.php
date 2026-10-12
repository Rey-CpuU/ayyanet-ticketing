@props(['customer' => null, 'fallback' => 'Unknown'])

{{-- Customer name with a subtle marker when the customer has been soft-deleted (Ticket::customer() includes trashed). --}}
<span {{ $attributes }}>{{ $customer->name ?? $fallback }}@if ($customer?->trashed())<span class="ml-1 text-[10.5px] font-medium text-[var(--muted)]" title="Customer ini sudah dihapus">(dihapus)</span>@endif</span>
