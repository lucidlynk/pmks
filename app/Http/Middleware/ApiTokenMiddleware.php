<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use App\Models\ApiTokenLog;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Ambil token dari header Authorization: Bearer {token}
        $bearerToken = $request->bearerToken();

        if (!$bearerToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak ditemukan. Sertakan header Authorization: Bearer {token}',
            ], 401);
        }

        // Cari token di database
        $accessToken = PersonalAccessToken::findToken($bearerToken);

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid.',
            ], 401);
        }

        // Cari api_client yang terkait
        $apiClient = ApiClient::where('token_id', $accessToken->id)
            ->where('is_active', true)
            ->first();

        if (!$apiClient) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak aktif atau sudah dinonaktifkan.',
            ], 403);
        }

        // Update last_used_at
        $apiClient->update(['last_used_at' => now()]);
        $accessToken->update(['last_used_at' => now()]);

        // Simpan api_client di request untuk dipakai controller
        $request->attributes->set('api_client', $apiClient);

        // Proses request
        $response = $next($request);

        // Log akses setelah response
        ApiTokenLog::create([
            'api_client_id' => $apiClient->id,
            'endpoint'      => $request->path(),
            'method'        => $request->method(),
            'parameters'    => $request->query() ?: null,
            'response_code' => $response->getStatusCode(),
            'ip_address'    => $request->ip(),
            'user_agent'    => $request->userAgent(),
            'accessed_at'   => now(),
        ]);

        return $response;
    }
}
