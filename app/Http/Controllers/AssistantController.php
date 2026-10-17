<?php

namespace App\Http\Controllers;

use App\Services\PortfolioAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AssistantController extends Controller
{
    public function __invoke(Request $request, PortfolioAssistant $assistant): JsonResponse
    {
        $data = $request->validate([
            'locale' => ['required', 'in:id,en'],
            'messages' => ['required', 'array', 'min:1', 'max:12'],
            'messages.*.role' => ['required', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:1000'],
        ]);

        // Only the last turns matter; keeps tokens (and cost) small
        $messages = array_slice(array_map(fn ($m) => [
            'role' => $m['role'],
            'content' => trim($m['content']),
        ], $data['messages']), -8);

        try {
            return response()->json($assistant->reply($messages, $data['locale']));
        } catch (Throwable) {
            return response()->json(['error' => __('portfolio.assistant.error', [], $data['locale'])], 503);
        }
    }
}
