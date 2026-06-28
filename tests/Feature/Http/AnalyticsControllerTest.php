<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

it('returns a success response for a valid request', function () {
    // The action's persistence (matching the request's session id) is covered
    // by UpdateSiteViewDurationActionTest. Here we assert the route validates
    // input and responds successfully.
    $this->post(route('analytics.duration'), [
        'url' => 'https://drafto.test/posts/hello',
        'duration' => 42,
    ])->assertOk()->assertJson(['status' => 'success']);
});

it('validates the url is required', function () {
    $this->postJson(route('analytics.duration'), [
        'duration' => 10,
    ])->assertStatus(422)->assertJsonValidationErrors(['url']);
});

it('validates the duration must be a non negative integer', function () {
    $this->postJson(route('analytics.duration'), [
        'url' => 'https://drafto.test/x',
        'duration' => -5,
    ])->assertStatus(422)->assertJsonValidationErrors(['duration']);
});

it('succeeds even when there is no matching site view', function () {
    $this->post(route('analytics.duration'), [
        'url' => 'https://drafto.test/none',
        'duration' => 10,
    ])->assertOk()->assertJson(['status' => 'success']);
});
