<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            $tickets = Ticket::latest()->paginate(100);
        } else {
            $tickets = $user->tickets()->latest()->paginate(10);
        }

        return view($user->hasRole('admin') ? 'admin.tickets.index' : 'user.tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view(Auth::user()->hasRole('admin') ? 'admin.tickets.create' : 'user.tickets.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'priority' => 'required|in:low,medium,high',
            'photos.*' => 'image|max:2048',
        ]);

        $ticket = Ticket::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'priority' => $request->priority,
            'status' => 'open',
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('tickets', 'public');
                $ticket->attachments()->create([
                    'file_path' => $path,
                ]);
            }
        }

        return redirect()->route(Auth::user()->hasRole('admin') ? 'admin.tickets.index' : 'user.tickets.index')
            ->with('success', 'Заявка успешно создана.');
    }

    public function show(Ticket $ticket)
    {
        $this->authorizeTicket($ticket);
        return view(Auth::user()->hasRole('admin') ? 'admin.tickets.show' : 'user.tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        $this->authorizeTicket($ticket);
        return view(Auth::user()->hasRole('admin') ? 'admin.tickets.edit' : 'user.tickets.edit', compact('ticket'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $this->authorizeTicket($ticket);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $ticket->update($request->only(['title', 'description', 'category', 'priority', 'status']));

        return redirect()->route(Auth::user()->hasRole('admin') ? 'admin.tickets.index' : 'user.tickets.index')
            ->with('success', 'Заявка обновлена.');
    }

    public function destroy(Ticket $ticket)
    {
        $this->authorizeTicket($ticket);
        $ticket->delete();
        return back()->with('success', 'Заявка удалена.');
    }

    protected function authorizeTicket(Ticket $ticket)
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($ticket->user_id !== $user->id) {
            abort(403, 'У вас нет доступа к этой заявке.');
        }
    }
}
