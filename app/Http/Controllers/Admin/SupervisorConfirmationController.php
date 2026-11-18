<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ValidateSupervisorPinRequest;
use App\Services\SupervisorAuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class SupervisorConfirmationController extends Controller
{
    public function __construct(
        protected SupervisorAuthorizationService $supervisors,
    ) {}

    /**
     * Validate a supervisor PIN and unlock sensitive actions for this session.
     */
    public function validatePin(ValidateSupervisorPinRequest $request): JsonResponse
    {
        try {
            $supervisor = $this->supervisors->authorizeByPin(
                (string) $request->validated('pin'),
                Auth::user(),
            );
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'valid' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        $expiresAt = $this->supervisors->startSession(
            $supervisor,
            (int) config('supervisor.session_minutes', 10),
        );

        activity('autorizacion')
            ->causedBy(Auth::user())
            ->event('authorized')
            ->withProperties([
                'supervisor_id' => $supervisor->id,
                'supervisor_name' => $supervisor->name,
            ])
            ->log("PIN de supervisor validado por {$supervisor->name}");

        return response()->json([
            'valid' => true,
            'supervisor_name' => $supervisor->name,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);
    }
}
