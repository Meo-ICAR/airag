<?php

namespace App\Http\Controllers;

use App\Models\ChatHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ChatHistoryController extends Controller
{
    /**
     * Display a listing of the chat history.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $userId = $request->input('user_id');
        $threadId = $request->input('thread_id');

        $query = ChatHistory::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($threadId) {
            $query->where('thread_id', $threadId);
        }

        $chats = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $chats
        ]);
    }

    /**
     * Store a newly created chat history in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'thread_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('chat_history', 'thread_id')
            ],
            'messages' => 'required|array',
            'messages.*.role' => 'required|string|in:user,assistant,system',
            'messages.*.content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $chat = ChatHistory::create([
            'user_id' => $request->input('user_id'),
            'thread_id' => $request->input('thread_id'),
            'messages' => $request->input('messages')
        ]);

        return response()->json([
            'success' => true,
            'data' => $chat
        ], 201);
    }

    /**
     * Display the specified chat history.
     */
    public function show(string $id): JsonResponse
    {
        $chat = ChatHistory::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $chat
        ]);
    }

    /**
     * Get chat history by thread ID.
     */
    public function findByThreadId(string $threadId): JsonResponse
    {
        $chat = ChatHistory::where('thread_id', $threadId)->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $chat
        ]);
    }

    /**
     * Update the specified chat history in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $chat = ChatHistory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'messages' => 'sometimes|array',
            'messages.*.role' => 'required_with:messages|string|in:user,assistant,system',
            'messages.*.content' => 'required_with:messages|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->has('messages')) {
            $chat->messages = $request->input('messages');
        }

        $chat->save();

        return response()->json([
            'success' => true,
            'data' => $chat
        ]);
    }

    /**
     * Append a message to the chat history.
     */
    public function appendMessage(Request $request, string $threadId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|string|in:user,assistant,system',
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $chat = ChatHistory::where('thread_id', $threadId)->firstOrFail();
        
        $messages = $chat->messages ?? [];
        $messages[] = [
            'role' => $request->input('role'),
            'content' => $request->input('content'),
            'timestamp' => now()->toDateTimeString()
        ];
        
        $chat->messages = $messages;
        $chat->save();

        return response()->json([
            'success' => true,
            'data' => $chat
        ]);
    }

    /**
     * Remove the specified chat history from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $chat = ChatHistory::findOrFail($id);
        $chat->delete();

        return response()->json([
            'success' => true,
            'message' => 'Chat history deleted successfully.'
        ]);
    }

    /**
     * Clear chat history for a specific thread.
     */
    public function clearThread(string $threadId): JsonResponse
    {
        $chat = ChatHistory::where('thread_id', $threadId)->firstOrFail();
        $chat->messages = [];
        $chat->save();

        return response()->json([
            'success' => true,
            'message' => 'Thread messages cleared successfully.'
        ]);
    }
}
