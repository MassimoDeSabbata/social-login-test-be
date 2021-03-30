<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    /**
     * Redirect the user to the Provider authentication page.
     *
     * @param $provider
     * @return JsonResponse
     */
    public function redirectToProvider($provider)
    {
        $validated = $this->validateProvider($provider);
        if (!is_null($validated)) {
            return $validated;
        }

        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Obtain the user information from Provider.
     *
     * @param $provider
     * @return JsonResponse
     */
    public function handleProviderCallback($provider)
    {

        error_log("SONO QUI");
        $validated = $this->validateProvider($provider);
        if (!is_null($validated)) {
            return $validated;
        }
        try {
            $user = Socialite::driver($provider)->stateless()->user();
        } catch (ClientException $exception) {
            return response()->json(['error' => 'Invalid credentials provided.'], 422);
        }

        $userCreated = User::firstOrCreate(
            [
                'email' => $user->getEmail()
            ],
            [
                'email_verified_at' => now(),
                'name' => $user->getName(),
                'status' => true,
                'avatar' => $provider === 'facebook' ? "FACEBOOK" :(string) $user->getAvatar(),
            ]
        );
        $userCreated->providers()->updateOrCreate(
            [
                'provider' => $provider,
                'provider_id' => $user->getId(),
            ],
            [
                'avatar' => $provider === 'facebook' ? "FACEBOOK" : $user->getAvatar()
            ]
        );
        $token = $userCreated->createToken('token-name')->plainTextToken;

        error_log("TOKEN:". $user->token);


        if($provider === 'facebook'){
            $this->saveFacebookProfileImage($user, $userCreated);
        }

        $userCreated['AccessToken'] = $token;

        return $userCreated;
    }

    protected function saveFacebookProfileImage($user, $userCreated) {
        $arrContextOptions=[ "http" => [
            "method" => "GET",
            "header" => "Authorization: Bearer ".$user->token
        ]];

        $fileContent = file_get_contents('https://graph.facebook.com/v10.0/'.(string)$user->getId().'/picture?width=500', false, stream_context_create($arrContextOptions));


        Storage::disk('local')->put('public/profilepic/'.$userCreated->id.'.jpg', $fileContent);
    }

    /**
     * @param $provider
     * @return JsonResponse
     */
    protected function validateProvider($provider)
    {
        if (!in_array($provider, ['facebook', 'github', 'google'])) {
            return response()->json(['error' => 'Please login using facebook, github or google'], 422);
        }
    }
}
