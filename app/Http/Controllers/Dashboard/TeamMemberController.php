<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\StoreWebpImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\TeamMemberRequest;
use App\Models\Member;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Dashboard team tab, the Laravel version of the legacy team_members/team_members_list.php:
 * list, create, edit and delete the members shown on the public team page (admin / finance roles).
 */
class TeamMemberController extends Controller
{
    public function __construct(private StoreWebpImage $storeWebpImage) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $members = Member::query()
            ->with('alumniHonor')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('mail', 'like', "%{$search}%")
                    ->orWhere('position_es', 'like', "%{$search}%")
                    ->orWhere('position_en', 'like', "%{$search}%");
            }))
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        return view('dashboard.team-members.index', [
            'members' => $members,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('dashboard.team-members.form', ['teamMember' => new Member([
            'position_es' => 'Miembro',
            'position_en' => 'Member',
            'board' => false,
            'active' => true,
        ])]);
    }

    public function store(TeamMemberRequest $request): RedirectResponse
    {
        try {
            $member = DB::transaction(function () use ($request): Member {
                $member = Member::create($request->memberAttributes());

                if ($request->hasFile('image')) {
                    $member->update(['image_path' => ($this->storeWebpImage)($request->file('image'), $this->folderFor($member))]);
                }

                $this->syncHonor($member, $request->honorAttributes());

                return $member;
            });
        } catch (Throwable $exception) {
            return $this->uploadFailed($exception);
        }

        return to_route('dashboard.team-members.index')
            ->with('status', __('Miembro «:name» creado.', ['name' => $member->full_name]));
    }

    public function edit(Member $member): View
    {
        return view('dashboard.team-members.form', ['teamMember' => $member->load('alumniHonor')]);
    }

    public function update(TeamMemberRequest $request, Member $member): RedirectResponse
    {
        $attributes = $request->memberAttributes();

        try {
            if ($request->hasFile('image')) {
                $attributes['image_path'] = ($this->storeWebpImage)($request->file('image'), $this->folderFor($member));
            }
        } catch (Throwable $exception) {
            return $this->uploadFailed($exception);
        }

        DB::transaction(function () use ($request, $member, $attributes): void {
            $member->update($attributes);
            $this->syncHonor($member, $request->honorAttributes());
        });

        return to_route('dashboard.team-members.index')
            ->with('status', __('Miembro «:name» actualizado.', ['name' => $member->full_name]));
    }

    /**
     * Members can't delete their own account from here, so nobody locks themselves out by accident.
     */
    public function destroy(Request $request, Member $member): RedirectResponse
    {
        if ($request->user()?->is($member)) {
            return to_route('dashboard.team-members.index')
                ->withErrors(['member' => __('No puedes eliminar tu propia cuenta.')]);
        }

        $name = $member->full_name;

        DB::transaction(function () use ($member): void {
            $member->alumniHonor()->delete();
            $member->delete();
        });

        return to_route('dashboard.team-members.index')
            ->with('status', __('Miembro «:name» eliminado.', ['name' => $name]));
    }

    /**
     * @param  array{graduation_year: int|null, honor_quote: string|null}|null  $honor
     */
    private function syncHonor(Member $member, ?array $honor): void
    {
        if ($honor === null) {
            $member->alumniHonor()->delete();

            return;
        }

        $member->alumniHonor()->updateOrCreate([], $honor);
    }

    private function folderFor(Member $member): string
    {
        return "members/{$member->id}";
    }

    private function uploadFailed(Throwable $exception): RedirectResponse
    {
        report($exception);

        $reason = class_basename($exception).': '.Str::limit($exception->getMessage(), 220);

        return back()
            ->withInput()
            ->withErrors(['image' => __('No se pudo guardar el miembro ni su foto. Inténtalo de nuevo y, si sigue fallando, avisa al equipo web. (Detalle: :reason)', ['reason' => $reason])]);
    }
}
