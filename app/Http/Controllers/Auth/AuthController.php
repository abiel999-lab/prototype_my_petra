<?php
namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function checkEmailAndPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::where('email', $email)->first();

        // Check if email exists
        if (!$user) {
            return response()->json([
                'emailExists' => false,
                'passwordCorrect' => false,
            ], 404);
        }

        // Check if the password is correct
        if (!Hash::check($password, $user->password)) {
            return response()->json([
                'emailExists' => true,
                'passwordCorrect' => false,
            ], 401);
        }

        return response()->json([
            'emailExists' => true,
            'passwordCorrect' => true,
        ], 200);
    }
}

