<?php

namespace Sndpbag\Sndppwa\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WebAuthnController extends Controller
{
    /**
     * Generate registration options
     */
    public function registerOptions(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $challenge = Str::random(32);
        
        // Store challenge in session
        session(['webauthn_challenge' => $challenge]);

        $options = [
            'challenge' => base64_encode($challenge),
            'rp' => [
                'name' => config('pwa.webauthn.relying_party_name'),
                'id' => config('pwa.webauthn.relying_party_id'),
            ],
            'user' => [
                'id' => base64_encode($user->id),
                'name' => $user->email,
                'displayName' => $user->name,
            ],
            'pubKeyCredParams' => [
                ['type' => 'public-key', 'alg' => -7],  // ES256
                ['type' => 'public-key', 'alg' => -257], // RS256
            ],
            'authenticatorSelection' => [
                'authenticatorAttachment' => 'platform',
                'userVerification' => 'required',
                'residentKey' => 'preferred',
            ],
            'timeout' => 60000,
            'attestation' => 'none',
        ];

        return response()->json($options);
    }

    /**
     * Verify and store credential
     */
    public function registerVerify(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $credential = $request->input('credential');
        $challenge = session('webauthn_challenge');

        if (!$challenge) {
            return response()->json(['error' => 'Invalid challenge'], 400);
        }

        // In production, you should verify the credential properly
        // This is a simplified version
        
        // Store credential in database
        $user->webauthn_credentials = json_encode([
            'id' => $credential['id'],
            'publicKey' => $credential['response']['publicKey'] ?? null,
            'counter' => 0,
        ]);
        $user->save();

        session()->forget('webauthn_challenge');

        return response()->json([
            'success' => true,
            'message' => 'Biometric authentication registered successfully',
        ]);
    }

    /**
     * Generate authentication options
     */
    public function loginOptions(Request $request): JsonResponse
    {
        $challenge = Str::random(32);
        
        // Store challenge in session
        session(['webauthn_challenge' => $challenge]);

        $options = [
            'challenge' => base64_encode($challenge),
            'rpId' => config('pwa.webauthn.relying_party_id'),
            'timeout' => 60000,
            'userVerification' => 'required',
        ];

        return response()->json($options);
    }

    /**
     * Verify authentication
     */
    public function loginVerify(Request $request): JsonResponse
    {
        $credential = $request->input('credential');
        $challenge = session('webauthn_challenge');

        if (!$challenge) {
            return response()->json(['error' => 'Invalid challenge'], 400);
        }

        // In production, verify the credential signature properly
        // Find user by credential ID and authenticate
        
        session()->forget('webauthn_challenge');

        return response()->json([
            'success' => true,
            'message' => 'Authentication successful',
        ]);
    }
}