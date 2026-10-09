<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with(['customer', 'creator'])->latest()->get();

        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        $customers = Customer::all();

        return view('tickets.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'title'       => 'required',
            'description' => 'required',
        ]);

        Ticket::create([
            'ticket_number' => 'TCK-' . time(),
            'customer_id'   => $request->customer_id,
            'created_by'    => Auth::id() ?? 1,
            'title'         => $request->title,
            'description'   => $request->description,
            'priority'      => $request->priority ?? 'Low',
            'status'        => 'Open',
        ]);

        return redirect('/tickets')
            ->with('success', 'Ticket berhasil dibuat');
    }

    public function show($id)
    {
        $ticket = Ticket::with(['customer', 'messages'])->findOrFail($id);

        return view('tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        //
    }

    public function update(Request $request, Ticket $ticket)
    {
        //
    }

    public function destroy(Ticket $ticket)
    {
        //
    }
}
