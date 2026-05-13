<?php

declare(strict_types=1);

namespace App\Http\Controllers\Newsletter;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class VerifySubscriberController extends Controller
{
    public function __invoke(Request $request): View
    {
        $subscriber = NewsletterSubscriber::where('email', $request->email)
            ->where('verification_token', $request->token)
            ->firstOrFail();

        $subscriber->update([
            'verified_at' => now(),
            'verification_token' => null,
        ]);

        return view('public.newsletter.confirmed');
    }
}
