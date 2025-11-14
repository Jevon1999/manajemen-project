<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

trait RespondsWithJson
{
    protected function success(mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    protected function error(string $message = 'Error', int $status = 400, $errors = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];
        if (!is_null($errors)) {
            $payload['errors'] = $errors;
        }
        return response()->json($payload, $status);
    }

    protected function resourceOk(JsonResource $resource, string $message = 'OK')
    {
        return $resource->additional([
            'success' => true,
            'message' => $message,
        ]);
    }

    protected function resourceCreated(JsonResource $resource, string $message = 'Created')
    {
        return $resource
            ->additional([
                'success' => true,
                'message' => $message,
            ])
            ->response()
            ->setStatusCode(201);
    }

    protected function collectionOk($collection, string $message = 'OK')
    {
        // $collection is a ResourceCollection (e.g., Resource::collection())
        return $collection->additional([
            'success' => true,
            'message' => $message,
        ]);
    }

    protected function successResource(JsonResource $resource, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $resource->resolve(request()),
        ], $status);
    }

    protected function successCollection(AnonymousResourceCollection $collection, string $message = 'OK', int $status = 200): JsonResponse
    {
        // Build the response to extract pagination (links/meta) if present
        $array = $collection->response()->getData(true);
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $array['data'] ?? $array,
        ];
        if (isset($array['links'])) $payload['links'] = $array['links'];
        if (isset($array['meta'])) $payload['meta'] = $array['meta'];
        return response()->json($payload, $status);
    }
}
