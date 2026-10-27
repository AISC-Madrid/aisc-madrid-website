<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * List the people signed up through the forms or newsletter (form_submissions).
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $users = FormSubmission::query()
            ->select(['id', 'full_name', 'email', 'newsletter', 'submitted_at'])
            ->search($search)
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('dashboard.users.index', [
            'users' => $users,
            'search' => $search,
        ]);
    }
}
