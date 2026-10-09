<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Support\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Customer::class);

        $search = Search::input($request, 'q');
        // Deleted customers are only visible to admins.
        $trashed = $request->user()->isAdmin() && in_array($request->query('trashed'), ['with', 'only'], true)
            ? $request->query('trashed')
            : '';

        $customers = Customer::query()
            ->withCount('tickets')
            ->when($trashed === 'with', fn (Builder $q) => $q->withTrashed())
            ->when($trashed === 'only', fn (Builder $q) => $q->onlyTrashed())
            ->when($search !== '', fn (Builder $q) => $this->applySearch($q, $search))
            ->latest()
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers', 'search', 'trashed'));
    }

    /**
     * Lightweight lookup for the customer picker on the ticket forms.
     */
    public function search(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Customer::class);

        $search = Search::input($request, 'q');

        // Soft-deleted customers are excluded (SoftDeletes scope): they cannot be picked for tickets.
        $customers = Customer::query()
            ->when($search !== '', fn (Builder $q) => $this->applySearch($q, $search))
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'customer_id', 'name', 'phone']);

        return response()->json($customers);
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        return Search::whereContains($query, ['name', 'email', 'phone', 'customer_id'], $search);
    }

    public function create()
    {
        $this->authorize('create', Customer::class);

        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        // The customer_id ("C-0001") is assigned by the Customer model once the row id is known.
        DB::transaction(fn () => Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'package' => $request->package,
        ]));

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil ditambahkan.');
    }

    public function show(Request $request, Customer $customer)
    {
        $this->authorize('view', $customer);

        // Ticket history is limited to the tickets the viewer may open (lapangan: own tickets only).
        $tickets = $customer->tickets()
            ->visibleTo($request->user())
            ->with('assignee:id,name')
            ->latest()
            ->latest('id')
            ->get();

        return view('customers.show', compact('customer', 'tickets'));
    }

    public function edit(Customer $customer)
    {
        $this->authorize('update', $customer);

        return view('customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil diupdate.');
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('delete', $customer);

        // Customers with unresolved tickets stay: their tickets still need the contact details.
        $openTickets = $customer->tickets()->whereNotIn('status', ['Solved', 'Closed'])->count();

        if ($openTickets > 0) {
            return redirect()->route('customers.index')
                ->with('error', "Customer tidak dapat dihapus karena masih memiliki {$openTickets} ticket yang belum selesai. Selesaikan atau tutup ticket tersebut terlebih dahulu.");
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil dihapus.');
    }

    public function restore(Customer $customer)
    {
        $this->authorize('restore', $customer);

        $customer->restore();

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil dipulihkan');
    }

    public function forceDelete(Customer $customer)
    {
        $this->authorize('forceDelete', $customer);

        // Tickets (including soft-deleted ones) must never be wiped implicitly by a customer purge.
        if ($customer->tickets()->withTrashed()->exists()) {
            return redirect()->route('customers.index')
                ->with('error', 'Customer tidak dapat dihapus permanen karena masih memiliki ticket. Hapus permanen ticket terkait terlebih dahulu.');
        }

        $customer->forceDelete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil dihapus secara permanen');
    }
}
