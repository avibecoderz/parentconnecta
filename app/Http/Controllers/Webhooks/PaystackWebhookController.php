<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Payments\PaystackWebhookHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaystackWebhookController extends Controller
{
    public function __invoke(Request $request, PaystackWebhookHandler $handler): JsonResponse
    {
        $result = $handler->handle(
            $request->getContent(),
            (string) $request->header('x-paystack-signature', ''),
        );

        return response()->json([
            'status' => $result['status'],
            'message' => $result['message'],
        ], $result['http_status']);
    }
}
