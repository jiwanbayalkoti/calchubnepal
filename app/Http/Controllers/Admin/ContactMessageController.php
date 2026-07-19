<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\BuildsDataTableResponse;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    use BuildsDataTableResponse;

    public function index(): View
    {
        return view('admin.contact-messages.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = ContactMessage::query();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return $this->toDataTableResponse(
            $request,
            $query,
            searchableColumns: ['name', 'email', 'subject', 'message'],
            orderableColumns: ['name', 'subject', 'status', 'created_at'],
            transform: function (ContactMessage $message) {
                return [
                    'id' => $message->id,
                    'name' => $message->name,
                    'email' => $message->email,
                    'subject' => $message->subject,
                    'status' => $message->status,
                    'created_at' => $message->created_at?->format('Y-m-d H:i'),
                ];
            }
        );
    }

    public function show(int $id): JsonResponse
    {
        $message = ContactMessage::findOrFail($id);
        $message->markAsRead();

        return response()->json(['data' => $message]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $message = ContactMessage::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['new', 'read', 'replied', 'archived'])],
        ]);

        if ($validated['status'] === 'replied') {
            $message->markAsReplied();
        } else {
            $message->update($validated);
        }

        return response()->json(['message' => 'Message status updated successfully.', 'data' => $message]);
    }

    public function destroy(int $id): JsonResponse
    {
        ContactMessage::findOrFail($id)->delete();

        return response()->json(['message' => 'Message deleted successfully.']);
    }
}
