<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $token = Str::random(60);
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => now(),
        ]);

        Mail::send('auth.emails.reset', ['token' => $token], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Password Reset Request');
        });

        return response()->json(['status' => 'Password reset link sent!']);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $passwordReset = DB::table('password_resets')->where('token', $request->token)->first();

        if (!$passwordReset) {
            return response()->json(['error' => 'Invalid token.'], 400);
        }

        $user = User::where('email', $passwordReset->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('token', $request->token)->delete();

        return response()->json(['status' => 'Password has been reset!']);
    }
}