<?php

declare(strict_types=1);

namespace App\Http\Controllers\Newsletter;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class UnsubscribeController extends Controller
{
    public function __invoke(Request $request, string $email): View
    {
        $subscriber = NewsletterSubscriber::where('email', $email)->first();

        if ($subscriber) {
            $subscriber->delete();
        }

        return view('public.newsletter.unsubscribed');
    }
}
