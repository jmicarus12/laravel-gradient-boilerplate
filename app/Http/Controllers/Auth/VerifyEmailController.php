<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifyEmailController extends Controller
{
    /** "Please verify your email" page — where the "verified" middleware sends unverified users. */
    public function notice(Request $request): RedirectResponse|View
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->route('home')
            : view('auth.verify-email');
    }

    /** The link from the email. EmailVerificationRequest checks the signed URL belongs to this user. */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill(); // marks verified + fires the Verified event (no-op if already verified)

        return redirect()->route('home')->with('success', __('Thanks! Your email is verified.'));
    }

    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', __('A new verification link has been sent to your email.'));
    }
}
