<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use DomainException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @tags Auth 🔒
 */
class AuthController extends Controller
{
    public function __construct(private readonly AuthService $service)
    {
    }

    /**
     * Login
     *
     * Resgata o token de acesso do usuário para a aplicação
     * 
     * @unauthenticated
     */
    public function login(LoginRequest $request)
    {
        try {
            $payload = $request->validated();
            $user = $this->service->login(
                $payload['email'],
                $payload['password']
            );

            $token = $user->createToken('api')->plainTextToken;

            return response()->json([
                'token_type' => 'Bearer',
                'access_token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ], Response::HTTP_OK);
        } catch (DomainException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                Response::HTTP_UNAUTHORIZED
            );
        }
    }
}
