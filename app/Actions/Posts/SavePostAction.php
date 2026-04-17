<?php

declare(strict_types=1);

namespace App\Actions\Posts;

use App\DTOs\SavePostData;
use App\Enums\PostStatusEnum;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class SavePostAction
{
    /**
     * Sênior: Executa a lógica de salvamento com suporte a relacionamentos e transação.
     */
    public function exec(User $user, SavePostData $dto, ?Post $post = null): Post
    {
        return DB::transaction(function () use ($user, $dto, $post) {
            // Removemos 'tags' dos dados principais pois trataremos separadamente
            $data = collect($dto->toArray())->except('tags')->toArray();

            if ($post) {
                $this->handleCoverImageCleanup($post, $dto->cover_image_path);
                
                // Se o status estiver mudando para publicado agora, definimos a data
                if ($dto->status === PostStatusEnum::PUBLISHED && $post->status !== PostStatusEnum::PUBLISHED) {
                    $data['published_at'] = now();
                }

                $post->update($data);
            } else {
                // Sênior: Trava de Segurança de Limite de Plano
                if ($dto->status === PostStatusEnum::PUBLISHED && $user->hasReachedPostLimit()) {
                    throw new \Exception('Limite de publicações mensais atingido para o seu plano.');
                }

                if ($dto->status === PostStatusEnum::DRAFT && $user->hasReachedDraftLimit()) {
                    throw new \Exception('Limite de rascunhos atingido.');
                }

                $post = $user->posts()->create(array_merge($data, [
                    'published_at' => $dto->status === PostStatusEnum::PUBLISHED ? now() : null,
                ]));
            }

            // Sincronização de Tags
            $post->tags()->sync($dto->tags);

            // Atualização de SEO
            $this->updateSEO($post, $dto);

            return $post->fresh(['category', 'tags']);
        });
    }

    /**
     * Updates SEO metadata for the post.
     */
    private function updateSEO(Post $post, SavePostData $dto): void
    {
        if ($dto->seo_title || $dto->seo_description) {
            $post->seo()->updateOrCreate(
                ['model_id' => $post->id, 'model_type' => $post->getMorphClass()],
                [
                    'title' => $dto->seo_title,
                    'description' => $dto->seo_description,
                ]
            );
        }
    }

    /**
     * Cleans up the old cover image if a new one is provided or if it's being removed.
     */
    private function handleCoverImageCleanup(Post $post, ?string $newPath): void
    {
        if ($newPath && $post->cover_image_path && $post->cover_image_path !== $newPath) {
            Storage::disk('public')->delete($post->cover_image_path);
        }
    }
}
