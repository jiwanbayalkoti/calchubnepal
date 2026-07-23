<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Http\Requests\Advertiser\UpdateProfileRequest;
use App\Services\Media\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(protected FileUploadService $uploads)
    {
    }

    public function edit(Request $request): View
    {
        $user = $request->user();
        $advertiser = $user->advertiser;
        abort_unless($advertiser, 403);
        $this->authorize('update', $advertiser);

        return view('advertiser.profile.edit', compact('user', 'advertiser'));
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $advertiser = $user->advertiser;
        abort_unless($advertiser, 403);
        $this->authorize('update', $advertiser);

        $data = $request->validated();

        $advertiser->fill([
            'company_name' => $data['company_name'],
            'contact_person' => $data['contact_person'],
            'phone' => $data['phone'] ?? null,
            'updated_by' => $user->id,
        ]);

        if ($request->hasFile('logo')) {
            if ($advertiser->logo) {
                $this->uploads->deletePublic($advertiser->logo);
            }
            $advertiser->logo = $this->uploads->storePublic($request->file('logo'), 'advertisers/logos');
        }

        $advertiser->save();

        $user->fill([
            'name' => $data['contact_person'],
            'phone' => $data['phone'] ?? $user->phone,
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => [
                'company_name' => $advertiser->company_name,
                'contact_person' => $advertiser->contact_person,
                'phone' => $advertiser->phone,
                'logo_url' => $advertiser->logo_url,
                'email' => $user->email,
            ],
        ]);
    }
}
