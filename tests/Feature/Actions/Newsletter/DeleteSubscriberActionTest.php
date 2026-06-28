<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Newsletter;

use App\Actions\Newsletter\DeleteSubscriberAction;
use App\Models\NewsletterSubscriber;

beforeEach(function () {
    $this->action = app(DeleteSubscriberAction::class);
});

it('deletes the subscriber', function () {
    $subscriber = NewsletterSubscriber::factory()->create();

    $this->action->exec($subscriber);

    $this->assertModelMissing($subscriber);
});
