<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\BrazilStateEnum;
use App\Enums\PostTypeEnum;
use App\Enums\ReportReasonEnum;
use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\Comment;
use App\Models\Module;
use App\Models\NewsletterSubscriber;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostView;
use App\Models\Profile;
use App\Models\Report;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class DatabaseSeeder extends Seeder
{
    private const int WRITERS_COUNT = 10;

    private const int READERS_COUNT = 60;

    private const int CATEGORIES_COUNT = 8;

    private const int TAGS_COUNT = 20;

    private const int POSTS_PER_WRITER_MIN = 4;

    private const int POSTS_PER_WRITER_MAX = 10;

    private const int COMMENTS_PER_POST_MIN = 0;

    private const int COMMENTS_PER_POST_MAX = 8;

    private const int FOLLOWS_PER_USER_MIN = 1;

    private const int FOLLOWS_PER_USER_MAX = 8;

    private const int SAVED_POSTS_PER_READER_MIN = 2;

    private const int SAVED_POSTS_PER_READER_MAX = 12;

    private const int LIKED_POSTS_PER_READER_MIN = 3;

    private const int LIKED_POSTS_PER_READER_MAX = 15;

    private const int LIKED_COMMENTS_PER_READER_MIN = 0;

    private const int LIKED_COMMENTS_PER_READER_MAX = 10;

    private const int REPORTS_COUNT = 20;

    public function run(): void
    {
        $this->command?->info('==========================================');
        $this->command?->info('Iniciando seed completo da plataforma...');
        $this->command?->info('==========================================');

        $this->command?->info('Fazendo a busca de estado e municipios fakes...');

        Http::fake([
            'servicodados.ibge.gov.br/api/v1/localidades/estados*' => Http::response(
                BrazilStateEnum::forIbgeMock(), 200,
            ),
            'servicodados.ibge.gov.br/api/v1/localidades/estados/*/municipios*' => Http::response([
                ['id' => 1, 'nome' => 'Cidade Exemplo 1'],
                ['id' => 2, 'nome' => 'Cidade Exemplo 2'],
            ], 200),
        ]);

        $this->seedModules();

        $admin = $this->seedAdmin();
        $writers = $this->seedWriters();
        $readers = $this->seedReaders();

        $categories = $this->seedCategories();
        $tags = $this->seedTags();

        $posts = $this->seedPosts($writers, $categories, $tags);
        $comments = $this->seedComments($posts, $writers, $readers);

        $this->seedPostViews($posts, $writers->merge($readers)->values());
        $this->seedFollowers($writers, $readers, $admin);
        $this->seedSavedPosts($readers, $posts);
        $this->seedPostLikes($readers, $posts);
        $this->seedCommentLikes($readers, $comments);
        $this->seedNewsletter($categories);
        $this->seedReports($posts, $comments, $readers, $admin);

        $this->command?->info('==========================================');
        $this->command?->info('Seed concluído com sucesso.');
        $this->command?->info(sprintf('Admin: %s', $admin->email));
        $this->command?->info(sprintf('Writers: %d', $writers->count()));
        $this->command?->info(sprintf('Readers: %d', $readers->count()));
        $this->command?->info(sprintf('Categorias: %d', $categories->count()));
        $this->command?->info(sprintf('Tags: %d', $tags->count()));
        $this->command?->info(sprintf('Posts: %d', $posts->count()));
        $this->command?->info(sprintf('Comentários: %d', $comments->count()));
        $this->command?->info('==========================================');
    }

    private function seedAdmin(): User
    {
        $this->command?->warn('Criando admin...');

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.test'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'role' => RoleEnum::SUPER_ADMIN,
                'status' => UserStatusEnum::ACTIVE,
                'email_verified_at' => now(),
            ],
        );

        if (!$admin->profile()->exists()) {
            $admin->profile()->create(
                Profile::factory()->make([
                    'user_id' => $admin->id,
                    'username' => 'admin',
                ])->toArray(),
            );
        }

        $this->command?->info("✔ Admin pronto: {$admin->email}");

        return $admin;
    }

    /**
     * @return Collection<int, User>
     */
    private function seedWriters(): Collection
    {
        $this->command?->warn(sprintf('Criando %d writers...', self::WRITERS_COUNT));

        $writers = User::factory()
            ->count(self::WRITERS_COUNT)
            ->writer()
            ->active()
            ->withProfile()
            ->create();

        $this->command?->info(sprintf('✔ %d writers criados.', $writers->count()));

        return $writers;
    }

    /**
     * @return Collection<int, User>
     */
    private function seedReaders(): Collection
    {
        $this->command?->warn(sprintf('Criando %d readers...', self::READERS_COUNT));

        $readers = User::factory()
            ->count(self::READERS_COUNT)
            ->reader()
            ->active()
            ->withProfile()
            ->create();

        $this->command?->info(sprintf('✔ %d readers criados.', $readers->count()));

        return $readers;
    }

    /**
     * @return Collection<int, PostCategory>
     */
    private function seedCategories(): Collection
    {
        $this->command?->warn('Criando categorias...');

        $defaultCategories = collect([
            ['name' => 'Tecnologia', 'slug' => 'tecnologia'],
            ['name' => 'Negócios', 'slug' => 'negocios'],
            ['name' => 'Produtividade', 'slug' => 'produtividade'],
            ['name' => 'Design', 'slug' => 'design'],
            ['name' => 'Programação', 'slug' => 'programacao'],
            ['name' => 'Carreira', 'slug' => 'carreira'],
            ['name' => 'Marketing', 'slug' => 'marketing'],
            ['name' => 'Opinião', 'slug' => 'opiniao'],
        ]);

        $categories = $defaultCategories->map(function (array $category): PostCategory {
            return PostCategory::query()->firstOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => fake()->sentence(),
                ],
            );
        });

        if ($categories->count() < self::CATEGORIES_COUNT) {
            $remaining = self::CATEGORIES_COUNT - $categories->count();

            $extra = PostCategory::factory()
                ->count($remaining)
                ->create();

            $categories = $categories->merge($extra);
        }

        $this->command?->info(sprintf('✔ %d categorias prontas.', $categories->count()));

        return $categories->values();
    }

    /**
     * @return Collection<int, Tag>
     */
    private function seedTags(): Collection
    {
        $this->command?->warn('Criando tags...');

        $defaultTags = collect([
            ['name' => 'laravel', 'slug' => 'laravel'],
            ['name' => 'livewire', 'slug' => 'livewire'],
            ['name' => 'php', 'slug' => 'php'],
            ['name' => 'javascript', 'slug' => 'javascript'],
            ['name' => 'css', 'slug' => 'css'],
            ['name' => 'arquitetura', 'slug' => 'arquitetura'],
            ['name' => 'backend', 'slug' => 'backend'],
            ['name' => 'frontend', 'slug' => 'frontend'],
            ['name' => 'startup', 'slug' => 'startup'],
            ['name' => 'produto', 'slug' => 'produto'],
        ]);

        $tags = $defaultTags->map(function (array $tag): Tag {
            return Tag::query()->firstOrCreate(
                ['slug' => $tag['slug']],
                ['name' => $tag['name']],
            );
        });

        if ($tags->count() < self::TAGS_COUNT) {
            $remaining = self::TAGS_COUNT - $tags->count();

            $extra = Tag::factory()
                ->count($remaining)
                ->create();

            $tags = $tags->merge($extra);
        }

        $this->command?->info(sprintf('✔ %d tags prontas.', $tags->count()));

        return $tags->values();
    }

    /**
     * @param  Collection<int, User>  $writers
     * @param  Collection<int, PostCategory>  $categories
     * @param  Collection<int, Tag>  $tags
     * @return Collection<int, Post>
     */
    private function seedPosts(Collection $writers, Collection $categories, Collection $tags): Collection
    {
        $this->command?->warn('Criando posts e vinculando tags...');

        $posts = collect();

        foreach ($writers as $writer) {
            $postsCount = fake()->numberBetween(
                self::POSTS_PER_WRITER_MIN,
                self::POSTS_PER_WRITER_MAX,
            );

            $writerPosts = Post::factory()
                ->count($postsCount)
                ->forAuthor($writer)
                ->published()
                ->create([
                    'category_id' => fn () => $categories->random()->id,
                    'type' => fn () => fake()->boolean(35)
                        ? PostTypeEnum::ARTICLE
                        : PostTypeEnum::POST,
                ]);

            foreach ($writerPosts as $post) {
                $post->tags()->syncWithoutDetaching(
                    $tags->random(fake()->numberBetween(2, 5))->pluck('id')->all(),
                );
            }

            $posts = $posts->merge($writerPosts);
        }

        $this->command?->info(sprintf('✔ %d posts criados.', $posts->count()));

        return $posts->values();
    }

    /**
     * @param  Collection<int, Post>  $posts
     * @param  Collection<int, User>  $writers
     * @param  Collection<int, User>  $readers
     * @return Collection<int, Comment>
     */
    private function seedComments(Collection $posts, Collection $writers, Collection $readers): Collection
    {
        $this->command?->warn('Criando comentários e respostas...');

        $allUsers = $writers->merge($readers)->values();
        $comments = collect();

        foreach ($posts as $post) {
            $commentsCount = fake()->numberBetween(
                self::COMMENTS_PER_POST_MIN,
                self::COMMENTS_PER_POST_MAX,
            );

            if ($commentsCount === 0) {
                continue;
            }

            $postComments = Comment::factory()
                ->count($commentsCount)
                ->forPost($post)
                ->create([
                    'user_id' => fn () => $allUsers->random()->id,
                ]);

            $comments = $comments->merge($postComments);

            $replies = $postComments
                ->shuffle()
                ->take((int) floor($postComments->count() / 2))
                ->map(function (Comment $comment) use ($allUsers): Comment {
                    return Comment::factory()
                        ->replyTo($comment)
                        ->byUser($allUsers->random())
                        ->create();
                });

            $comments = $comments->merge($replies);

            $post->update([
                'comments_count' => $post->comments()->count(),
            ]);
        }

        $this->command?->info(sprintf('✔ %d comentários criados.', $comments->count()));

        return $comments->values();
    }

    private function seedNewsletter(Collection $categories): void
    {
        $this->command?->warn('Criando assinantes da newsletter...');

        foreach (range(1, 30) as $i) {
            NewsletterSubscriber::query()
                ->create([
                    'email' => fake()->unique()->safeEmail(),
                    'category_id' => $categories->random()->id,
                ]);
        }
    }

    /**
     * @param  Collection<int, Post>  $posts
     * @param  Collection<int, User>  $users
     */
    private function seedPostViews(Collection $posts, Collection $users): void
    {
        $this->command?->warn('Criando visualizações de posts...');

        foreach ($posts as $post) {
            $viewsCount = fake()->numberBetween(10, 120);

            $authenticated = (int) floor($viewsCount * 0.6);
            $anonymous = $viewsCount - $authenticated;

            for ($i = 0; $i < $authenticated; $i++) {
                PostView::factory()
                    ->forPost($post)
                    ->byUser($users->random())
                    ->create();
            }

            for ($i = 0; $i < $anonymous; $i++) {
                PostView::factory()
                    ->forPost($post)
                    ->anonymous()
                    ->create();
            }

            $post->update([
                'views_count' => $viewsCount,
            ]);
        }

        $this->command?->info('✔ Visualizações criadas.');
    }

    /**
     * @param  Collection<int, User>  $writers
     * @param  Collection<int, User>  $readers
     */
    private function seedFollowers(Collection $writers, Collection $readers, User $admin): void
    {
        $this->command?->warn('Criando relações de seguidores...');

        foreach ($readers as $reader) {
            $targets = $writers
                ->merge($readers->where('id', '!=', $reader->id))
                ->shuffle()
                ->take(fake()->numberBetween(
                    self::FOLLOWS_PER_USER_MIN,
                    self::FOLLOWS_PER_USER_MAX,
                ));

            $reader->following()->syncWithoutDetaching($targets->pluck('id')->all());
        }

        foreach ($writers as $writer) {
            $targets = $writers
                ->where('id', '!=', $writer->id)
                ->shuffle()
                ->take(fake()->numberBetween(1, 4));

            $writer->following()->syncWithoutDetaching($targets->pluck('id')->all());
        }

        $adminTargets = $writers->shuffle()->take(3);
        $admin->following()->syncWithoutDetaching($adminTargets->pluck('id')->all());

        $this->command?->info('✔ Relações de seguidores criadas.');
    }

    /**
     * @param  Collection<int, User>  $readers
     * @param  Collection<int, Post>  $posts
     */
    private function seedSavedPosts(Collection $readers, Collection $posts): void
    {
        $this->command?->warn('Criando posts salvos...');

        foreach ($readers as $reader) {
            $saved = $posts
                ->shuffle()
                ->take(fake()->numberBetween(
                    self::SAVED_POSTS_PER_READER_MIN,
                    self::SAVED_POSTS_PER_READER_MAX,
                ));

            $reader->savedPosts()->syncWithoutDetaching($saved->pluck('id')->all());
        }

        $this->command?->info('✔ Posts salvos criados.');
    }

    /**
     * @param  Collection<int, User>  $readers
     * @param  Collection<int, Post>  $posts
     */
    private function seedPostLikes(Collection $readers, Collection $posts): void
    {
        $this->command?->warn('Criando curtidas em posts...');

        foreach ($readers as $reader) {
            $liked = $posts
                ->shuffle()
                ->take(fake()->numberBetween(
                    self::LIKED_POSTS_PER_READER_MIN,
                    self::LIKED_POSTS_PER_READER_MAX,
                ));

            $reader->likedPosts()->syncWithoutDetaching($liked->pluck('id')->all());
        }

        Post::query()->each(function (Post $post): void {
            $post->update([
                'likes_count' => $post->likedByUsers()->count(),
            ]);
        });

        $this->command?->info('✔ Curtidas em posts criadas.');
    }

    /**
     * @param  Collection<int, User>  $readers
     * @param  Collection<int, Comment>  $comments
     */
    private function seedCommentLikes(Collection $readers, Collection $comments): void
    {
        $this->command?->warn('Criando curtidas em comentários...');

        foreach ($readers as $reader) {
            if ($comments->isEmpty()) {
                break;
            }

            $liked = $comments
                ->shuffle()
                ->take(fake()->numberBetween(
                    self::LIKED_COMMENTS_PER_READER_MIN,
                    self::LIKED_COMMENTS_PER_READER_MAX,
                ));

            $reader->likedComments()->syncWithoutDetaching($liked->pluck('id')->all());
        }

        $this->command?->info('✔ Curtidas em comentários criadas.');
    }

    /**
     * @param  Collection<int, Post>  $posts
     * @param  Collection<int, Comment>  $comments
     * @param  Collection<int, User>  $readers
     */
    private function seedReports(
        Collection $posts,
        Collection $comments,
        Collection $readers,
        User $admin,
    ): void {
        $this->command?->warn('Criando denúncias...');

        $reportables = $posts
            ->map(fn (Post $post): array => [
                'type' => Post::class,
                'id' => $post->id,
            ])
            ->merge(
                $comments->map(fn (Comment $comment): array => [
                    'type' => Comment::class,
                    'id' => $comment->id,
                ]),
            )
            ->shuffle()
            ->take(self::REPORTS_COUNT)
            ->values();

        foreach ($reportables as $item) {
            $reporter = $readers->random();

            Report::factory()
                ->byReporter($reporter)
                ->reason(fake()->randomElement(ReportReasonEnum::cases()))
                ->create([
                    'reportable_type' => $item['type'],
                    'reportable_id' => $item['id'],
                    'reviewed_by' => fake()->boolean(35) ? $admin->id : null,
                    'reviewed_at' => fake()->boolean(35)
                        ? now()->subDays(fake()->numberBetween(1, 10))
                        : null,
                ]);
        }

        $this->command?->info('✔ Denúncias criadas.');
    }

    private function seedModules(): void
    {
        $this->command?->warn('Semeando módulos...');

        $modules = config('modules');

        foreach ($modules as $module) {
            Module::query()->updateOrCreate(
                ['slug' => $module['slug']],
                [
                    'name' => $module['name'],
                    'description' => $module['description'],
                    'icon' => $module['icon'],
                    'is_enabled' => true,
                    'settings' => $module['settings'],
                ],
            );
        }

        $this->command?->info('✔ ' . count($modules) . ' módulos semeados.');
    }
}
