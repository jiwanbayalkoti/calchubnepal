<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\BuildsDataTableResponse;
use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    use BuildsDataTableResponse;

    public function index(): View
    {
        return view('admin.feedback.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = Feedback::query()->with(['user', 'calculator']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return $this->toDataTableResponse(
            $request,
            $query,
            searchableColumns: ['message', 'type'],
            orderableColumns: ['type', 'rating', 'status', 'created_at'],
            transform: function (Feedback $feedback) {
                return [
                    'id' => $feedback->id,
                    'user' => $feedback->user?->name ?? 'Guest',
                    'calculator' => $feedback->calculator?->title,
                    'rating' => $feedback->rating,
                    'message' => str($feedback->message)->limit(80)->toString(),
                    'type' => $feedback->type,
                    'status' => $feedback->status,
                    'created_at' => $feedback->created_at?->format('Y-m-d H:i'),
                ];
            }
        );
    }

    public function show(int $id): JsonResponse
    {
        $feedback = Feedback::with(['user', 'calculator'])->findOrFail($id);

        return response()->json(['data' => $feedback]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $feedback = Feedback::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['new', 'reviewed', 'resolved'])],
        ]);

        $feedback->update($validated);

        return response()->json(['message' => 'Feedback status updated successfully.', 'data' => $feedback]);
    }

    public function destroy(int $id): JsonResponse
    {
        Feedback::findOrFail($id)->delete();

        return response()->json(['message' => 'Feedback deleted successfully.']);
    }
}
