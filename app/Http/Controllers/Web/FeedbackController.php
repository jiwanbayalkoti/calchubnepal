<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\FeedbackRequest;
use App\Models\Feedback;
use App\Notifications\Admin\FeedbackReceived;
use App\Services\Admin\AdminNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

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

        $this->notifier->notify(new FeedbackReceived($feedback));

        $message = 'Thanks for your feedback — we appreciate it!';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('status', $message);
    }
}
