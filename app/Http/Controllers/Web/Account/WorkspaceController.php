<?php

namespace App\Http\Controllers\Web\Account;

use App\Http\Controllers\Controller;
use App\Models\QrWorkspace;
use App\Models\QrWorkspaceMember;
use App\Services\Qr\EnterpriseAnalyticsService;
use App\Services\Qr\QrEntitlementService;
use App\Services\Qr\WorkspaceService;
use App\Services\Seo\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class WorkspaceController extends Controller
{
    public function __construct(
        protected WorkspaceService $workspaces,
        protected EnterpriseAnalyticsService $analytics,
        protected QrEntitlementService $entitlements,
        protected SeoService $seo,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $list = $this->entitlements->canUseWorkspaces($user)
            ? $this->workspaces->forUser($user)
            : collect();

        return view('account.workspaces.index', [
            'user' => $user,
            'workspaces' => $list,
            'canCreate' => $this->entitlements->canUseWorkspaces($user),
            'meta' => $this->seo->buildMeta(null, [
                'title' => 'Workspaces — CalchubNepal',
                'canonical' => route('account.workspaces.index'),
                'robots' => 'noindex,nofollow',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'brand_primary' => ['nullable', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'brand_secondary' => ['nullable', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'support_email' => ['nullable', 'email'],
            'white_label_enabled' => ['sometimes', 'boolean'],
            'custom_domain' => ['nullable', 'string', 'max:190'],
            'redirect_footer' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $workspace = $this->workspaces->create($request->user(), array_merge($data, [
                'white_label_enabled' => $request->boolean('white_label_enabled'),
            ]));
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('account.workspaces.show', $workspace)->with('status', 'workspace-created');
    }

    public function show(Request $request, QrWorkspace $workspace): View
    {
        abort_unless($workspace->memberRoleFor($request->user()), 403);
        $report = $this->analytics->workspaceOverview($workspace);

        return view('account.workspaces.show', [
            'user' => $request->user(),
            'workspace' => $workspace->load(['members.user']),
            'report' => $report,
            'role' => $workspace->memberRoleFor($request->user()),
            'meta' => $this->seo->buildMeta(null, [
                'title' => $workspace->name.' — Workspace',
                'robots' => 'noindex,nofollow',
            ]),
        ]);
    }

    public function update(Request $request, QrWorkspace $workspace): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'brand_primary' => ['nullable', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'brand_secondary' => ['nullable', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'support_email' => ['nullable', 'email'],
            'white_label_enabled' => ['sometimes', 'boolean'],
            'custom_domain' => ['nullable', 'string', 'max:190'],
            'redirect_footer' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->workspaces->update($workspace, $request->user(), array_merge($data, [
                'white_label_enabled' => $request->boolean('white_label_enabled'),
            ]));
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'workspace-updated');
    }

    public function invite(Request $request, QrWorkspace $workspace): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['required', 'in:admin,member,viewer'],
        ]);

        try {
            $this->workspaces->invite($workspace, $request->user(), $data['email'], $data['role']);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'member-invited');
    }

    public function updateMember(Request $request, QrWorkspace $workspace, QrWorkspaceMember $member): RedirectResponse
    {
        $data = $request->validate(['role' => ['required', 'in:admin,member,viewer']]);
        try {
            $this->workspaces->updateMemberRole($workspace, $request->user(), $member, $data['role']);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'member-updated');
    }

    public function removeMember(Request $request, QrWorkspace $workspace, QrWorkspaceMember $member): RedirectResponse
    {
        try {
            $this->workspaces->removeMember($workspace, $request->user(), $member);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'member-removed');
    }
}
