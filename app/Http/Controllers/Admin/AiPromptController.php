<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\BuildsDataTableResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AiPromptRequest;
use App\Models\AiPrompt;
use App\Services\Activity\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AiPromptController extends Controller
{
    use BuildsDataTableResponse;

    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    public function index(): View
    {
        return view('admin.ai-prompts.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = AiPrompt::query()->withCount('logs');

        return $this->toDataTableResponse(
            $request,
            $query,
            searchableColumns: ['name', 'purpose', 'provider'],
            orderableColumns: ['name', 'purpose', 'provider', 'is_active', 'logs_count', 'created_at'],
            transform: function (AiPrompt $prompt) {
                return [
                    'id' => $prompt->id,
                    'name' => $prompt->name,
                    'purpose' => $prompt->purpose,
                    'provider' => $prompt->provider,
                    'model' => $prompt->model,
                    'is_active' => (bool) $prompt->is_active,
                    'logs_count' => $prompt->logs_count,
                ];
            }
        );
    }

    public function store(AiPromptRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['is_active'] = $data['is_active'] ?? true;
        $data['created_by'] = $request->user()?->id;

        $prompt = AiPrompt::create($data);

        $this->activityLog->log('create', 'ai_prompts', $prompt, ['name' => $prompt->name]);

        return response()->json(['message' => 'AI prompt created successfully.', 'data' => $prompt], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['data' => AiPrompt::findOrFail($id)]);
    }

    public function update(AiPromptRequest $request, int $id): JsonResponse
    {
        $prompt = AiPrompt::findOrFail($id);

        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['updated_by'] = $request->user()?->id;

        $prompt->update($data);

        $this->activityLog->log('update', 'ai_prompts', $prompt, ['name' => $prompt->name]);

        return response()->json(['message' => 'AI prompt updated successfully.', 'data' => $prompt]);
    }

    public function destroy(int $id): JsonResponse
    {
        $prompt = AiPrompt::findOrFail($id);
        $name = $prompt->name;
        $prompt->delete();

        $this->activityLog->log('delete', 'ai_prompts', null, ['name' => $name]);

        return response()->json(['message' => 'AI prompt deleted successfully.']);
    }
}
