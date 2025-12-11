<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\AuditLog;
use Symfony\Component\HttpFoundation\Response;

class LogSensitiveActions
{
    /**
     * Actions yang perlu dilog
     */
    protected $sensitiveActions = [
        'store', 'update', 'destroy', 'login', 'logout'
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log hanya untuk aksi sensitif
        $action = $request->route()?->getActionMethod();
        
        if (in_array($action, $this->sensitiveActions)) {
            $this->logAction($request, $response);
        }

        return $response;
    }

    /**
     * Log the sensitive action
     */
    protected function logAction(Request $request, Response $response)
    {
        $user = $request->user();
        $action = $request->route()?->getActionMethod();
        $controller = $request->route()?->getActionName();
        
        // Log ke file (existing)
        Log::info('Sensitive Action Performed', [
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'user_role' => $user?->role,
            'action' => $action,
            'controller' => $controller,
            'route' => $request->route()?->getName(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status_code' => $response->getStatusCode(),
            'timestamp' => now()->toISOString(),
        ]);
        
        // Simpan ke database
        try {
            AuditLog::create([
                'user_id' => $user?->id,
                'user_email' => $user?->email,
                'user_role' => $user?->role,
                'action' => $action,
                'controller' => $controller,
                'route' => $request->route()?->getName(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status_code' => $response->getStatusCode(),
                'request_data' => $this->getRequestData($request),
                'performed_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save audit log: ' . $e->getMessage());
        }
    }
    
    /**
     * Get relevant request data (tanpa password)
     */
    protected function getRequestData(Request $request): array
    {
        $data = $request->except(['password', 'password_confirmation', '_token', '_method']);
        
        // Limit ukuran data yang disimpan
        return array_slice($data, 0, 10, true);
    }
}
