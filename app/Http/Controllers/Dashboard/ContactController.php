<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\ContactRequest;
use App\Models\Contact;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard contacts tab, the Laravel version of the legacy dashboard/contacts/:
 * list, search, filter by category, create, edit, delete and see the emails sent to each contact.
 */
class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $category = trim((string) $request->query('category', ''));

        $contacts = Contact::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('organization', 'like', "%{$search}%");
            }))
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->orderBy('full_name')
            ->paginate(25)
            ->withQueryString();

        return view('dashboard.contacts.index', [
            'contacts' => $contacts,
            'search' => $search,
            'category' => $category,
            'categories' => $this->categories(),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.contacts.form', [
            'contact' => new Contact(['active' => true]),
            'categories' => $this->categories(),
        ]);
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        $contact = Contact::create($request->validated());

        return to_route('dashboard.contacts.index')
            ->with('status', __('Contacto «:name» creado.', ['name' => $contact->full_name]));
    }

    public function show(Contact $contact): View
    {
        $contact->load(['emailLogs' => fn ($query) => $query->orderByDesc('sent_at')]);

        return view('dashboard.contacts.show', ['contact' => $contact]);
    }

    public function edit(Contact $contact): View
    {
        return view('dashboard.contacts.form', [
            'contact' => $contact,
            'categories' => $this->categories(),
        ]);
    }

    public function update(ContactRequest $request, Contact $contact): RedirectResponse
    {
        $contact->update($request->validated());

        return to_route('dashboard.contacts.index')
            ->with('status', __('Contacto «:name» actualizado.', ['name' => $contact->full_name]));
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $name = $contact->full_name;

        DB::transaction(function () use ($contact): void {
            $contact->emailLogs()->delete();
            $contact->delete();
        });

        return to_route('dashboard.contacts.index')
            ->with('status', __('Contacto «:name» eliminado.', ['name' => $name]));
    }

    /**
     * @return Collection<int, string>
     */
    private function categories(): Collection
    {
        return Contact::query()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
    }
}
