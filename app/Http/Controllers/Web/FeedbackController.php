<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\FeedbackRequest;
use App\Models\Feedback;
use App\Notifications\Admin\FeedbackReceived;
use App\Services\Admin\AdminNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class FeedbackController extends Controller
{
    public function __construct(protected AdminNotifier $notifier)
    {
    }

    public function store(FeedbackRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()?->id;
        $data['ip_address'] = $request->ip();
        $data['status'] = Feedback::STATUS_NEW;
        $data['type'] = $data['type'] ?? 'general';

        $feedback = Feedback::create($data);
        $feedback->load(['user', 'calculator']);

        try {
            $this->notifier->notify(new FeedbackReceived($feedback));
        } catch (Throwable $e) {
            Log::error('Failed to notify admins of feedback.', [
                'feedback_id' => $feedback->id,
                'error' => $e->getMessage(),
            ]);
        }

        $message = 'Thanks for your feedback — we appreciate it!';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('status', $message);
    }
}
