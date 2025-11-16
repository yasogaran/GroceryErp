<?php

namespace App\Http\Middleware;

use App\Services\ShiftService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireActiveShift
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $shiftService = app(ShiftService::class);

        if (!$shiftService->hasOpenShift(auth()->user())) {
            return redirect()->route('shift.open')
                ->with('error', 'Please open a shift to continue.');
        }

        return $next($request);
    }
}
