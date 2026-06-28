<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Public\Site;

use App\Enums\ProfileVisibilityEnum;
use App\Livewire\Public\Site\ExploreWriters;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    Cache::flush();
});

/**
 * The explore-writers query only returns profiles whose name/username/email
 * columns are populated and whose visibility is public.
 */
function makePublicWriter(string $name): User
{
    $user = User::factory()->active()->withProfile()->create(['name' => $name]);

    $user->profile->update([
        'name' => $name,
        'email' => $user->email,
        'visibility' => ProfileVisibilityEnum::PUBLIC,
    ]);

    return $user;
}

it('renders successfully', function () {
    Livewire::test(ExploreWriters::class)
        ->assertOk()
        ->assertSet('search', '');
});

it('lists active public writers', function () {
    $writer = makePublicWriter('Visible Writer');
    Post::factory()->published()->for($writer)->create();

    Cache::flush();

    Livewire::test(ExploreWriters::class)
        ->assertSet('writers', fn ($writers) => $writers->contains('id', $writer->id));
});

it('filters writers by search term', function () {
    $matching = makePublicWriter('Aurelius Matchington');
    $other = makePublicWriter('Someone Else Entirely');

    Cache::flush();

    Livewire::test(ExploreWriters::class)
        ->set('search', 'Aurelius')
        ->assertSet('writers', fn ($writers) => $writers->contains('id', $matching->id)
            && !$writers->contains('id', $other->id));
});

it('resets pagination when search is updated', function () {
    foreach (range(1, 3) as $i) {
        makePublicWriter("Writer Number {$i}");
    }

    Cache::flush();

    Livewire::test(ExploreWriters::class)
        ->call('setPage', 2)
        ->assertSet('paginators.page', 2)
        ->set('search', 'whatever')
        ->assertSet('paginators.page', 1);
});

it('renders the lazy placeholder view', function () {
    expect(view('livewire.public.site.placeholders.explore-writers')->render())->toBeString();
});
