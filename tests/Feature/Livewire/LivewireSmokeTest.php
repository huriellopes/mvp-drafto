<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Enums\PostVisibilityEnum;
use App\Livewire\Actions\FollowButton;
use App\Livewire\Actions\SaveButton;
use App\Livewire\Auth\ResendVerification;
use App\Livewire\Auth\VerifyEmailNotice;
use App\Livewire\Dashboard\Admin\Analytics\SiteAnalytics;
use App\Livewire\Dashboard\Admin\AuditDetails;
use App\Livewire\Dashboard\Admin\AuditLogIndex;
use App\Livewire\Dashboard\Admin\Modules\ModuleIndex;
use App\Livewire\Dashboard\Admin\Newsletter\NewsletterIndex;
use App\Livewire\Dashboard\Admin\Reports\ReportIndex;
use App\Livewire\Dashboard\Admin\ShortLinkIndex;
use App\Livewire\Dashboard\Admin\Support\SupportIndex;
use App\Livewire\Dashboard\Auth\ForceChangePassword;
use App\Livewire\Dashboard\Comments\CommentIndex;
use App\Livewire\Dashboard\Follows\FollowIndex;
use App\Livewire\Dashboard\ImpersonationBanner;
use App\Livewire\Dashboard\Index;
use App\Livewire\Dashboard\Modules\LinkShortenerDashboard;
use App\Livewire\Dashboard\NotificationBell;
use App\Livewire\Dashboard\NotificationsSidebar;
use App\Livewire\Dashboard\Posts\CoverUpload;
use App\Livewire\Dashboard\Posts\DraftIndex;
use App\Livewire\Dashboard\Posts\IndexPosts;
use App\Livewire\Dashboard\Posts\ManagePost;
use App\Livewire\Dashboard\Profile\ProfileBadgeGenerator;
use App\Livewire\Dashboard\ProfileStatus;
use App\Livewire\Dashboard\Support\SupportPage;
use App\Livewire\Dashboard\Widgets\ProfileInfo;
use App\Livewire\Dashboard\Widgets\RecentActivity;
use App\Livewire\Dashboard\Widgets\StatsOverview;
use App\Livewire\Dashboard\Widgets\SuggestedWriters;
use App\Livewire\Public\NewsletterForm;
use App\Livewire\Public\Posts\ShowPost;
use App\Livewire\Public\ReportModal;
use App\Livewire\Public\Site\ExplorePosts;
use App\Livewire\Public\Site\ExploreWriters;
use App\Livewire\Public\Site\GlobalSearch;
use App\Models\Post;
use App\Models\User;
use Livewire\Livewire;

/**
 * Testes de fumaça: garantem que TODO componente Livewire monta e renderiza
 * sem erro (integridade). Complementam os testes de comportamento específicos.
 */

// --- Componentes do dashboard/admin (autenticado como super admin) ---
it('renders authenticated dashboard/admin components without error', function (string $component) {
    $admin = User::factory()->superAdmin()->withProfile()->create();

    Livewire::actingAs($admin)->test($component)->assertOk();
})->with([
    Index::class,
    NotificationBell::class,
    NotificationsSidebar::class,
    ProfileStatus::class,
    ImpersonationBanner::class,
    FollowIndex::class,
    SupportPage::class,
    IndexPosts::class,
    DraftIndex::class,
    ManagePost::class,
    CoverUpload::class,
    ProfileBadgeGenerator::class,
    LinkShortenerDashboard::class,
    CommentIndex::class,
    SuggestedWriters::class,
    ProfileInfo::class,
    StatsOverview::class,
    RecentActivity::class,
    ShortLinkIndex::class,
    AuditLogIndex::class,
    AuditDetails::class,
    ModuleIndex::class,
    SupportIndex::class,
    SiteAnalytics::class,
    ReportIndex::class,
    NewsletterIndex::class,
]);

// --- Componentes públicos (sem autenticação) ---
it('renders public components without error', function (string $component) {
    Livewire::test($component)->assertOk();
})->with([
    NewsletterForm::class,
    ReportModal::class,
    ExploreWriters::class,
    GlobalSearch::class,
    ExplorePosts::class,
]);

// --- Componentes com parâmetros / contexto específico ---
it('renders the public post component', function () {
    $writer = User::factory()->writer()->withProfile()->create();
    $post = Post::factory()->published()->for($writer)->create(['visibility' => PostVisibilityEnum::PUBLIC]);

    Livewire::test(ShowPost::class, ['slug' => $post->slug])->assertOk();
});

it('renders the follow button for a target user', function () {
    $viewer = User::factory()->create();
    $target = User::factory()->writer()->withProfile()->create();

    Livewire::actingAs($viewer)->test(FollowButton::class, ['user' => $target])->assertOk();
});

it('renders the save button for a post', function () {
    $viewer = User::factory()->create();
    $post = Post::factory()->published()->create();

    Livewire::actingAs($viewer)->test(SaveButton::class, ['post' => $post])->assertOk();
});

it('renders the email verification components for an unverified user', function (string $component) {
    $user = User::factory()->unverified()->create();

    Livewire::actingAs($user)->test($component)->assertOk();
})->with([
    'notice' => VerifyEmailNotice::class,
    'resend' => ResendVerification::class,
]);

it('renders the force change password component', function () {
    $user = User::factory()->create(['must_change_password' => true]);

    Livewire::actingAs($user)->test(ForceChangePassword::class)->assertOk();
});
