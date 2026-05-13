<?php

declare(strict_types=1);

namespace App\Actions\Posts;

use App\DTOs\SavePostData;
use App\Enums\ModuleEnum;
use App\Enums\PostStatusEnum;
use App\Events\Posts\PostSaved;
use App\Models\Module;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

final class SavePostAction
{
    /**
     * Sênior: Executa a lógica de salvamento com suporte a relacionamentos e transação.
     */
    public function exec(User $user, SavePostData $dto, ?Post $post = null): Post
    {
        $oldImagePath = $post?->cover_image_path;

        /** @var Post $savedPost */
        $savedPost = DB::transaction(function () use ($user, $dto, $post, $oldImagePath) {
            // Sênior: Trava de Segurança de Limite de Plano
            // Dispara se estiver publicando um novo post OU transformando um rascunho em publicado.
            $isPublishing = $dto->status === PostStatusEnum::PUBLISHED && (!$post || $post->status !== PostStatusEnum::PUBLISHED);
            $isCreatingDraft = $dto->status === PostStatusEnum::DRAFT && !$post;

            if ($isPublishing && $user->hasReachedPostLimit()) {
                throw new Exception('Você atingiu o limite de publicações mensais do seu plano.');
            }

            if ($isCreatingDraft && $user->hasReachedDraftLimit()) {
                throw new Exception('Você atingiu o limite de rascunhos do seu plano.');
            }

            // Sênior: Validação de Segurança de Categoria (IDOR)
            $category = \App\Models\PostCategory::find($dto->category_id);
            if ($category && $category->user_id !== null && $category->user_id !== $user->id) {
                throw new Exception('A categoria selecionada é inválida ou você não tem permissão para usá-la.');
            }

            // Sênior: Sanitização de Segurança contra XSS
            $sanitizedContent = Purifier::clean($dto->content);
            $sanitizedExcerpt = $dto->excerpt ? Purifier::clean($dto->excerpt) : null;

            // Removemos tags e dados de SEO para processar separadamente/background
            $data = collect($dto->toArray())->except(['tags', 'seo_title', 'seo_description'])->toArray();
            $data['content'] = $sanitizedContent;
            $data['excerpt'] = $sanitizedExcerpt;

            if ($post) {
                // Se o status estiver mudando para publicado agora, definimos a data
                if ($isPublishing) {
                    $data['published_at'] = now();
                }

                $post->update($data);
            } else {
                /** @var Post $post */
                $post = $user->posts()->create(array_merge($data, [
                    'published_at' => $isPublishing ? now() : null,
                ]));
            }

            // Sincronização de Tags (Sênior: Processa tags existentes e novas)
            $processedTags = $this->processTags($user, $dto->tags);
            $post->tags()->sync($processedTags);

            // Sênior: Despacha evento para hooks externos (SEO, Imagem, Notificações)
            event(new PostSaved($post, [
                'title' => $dto->seo_title,
                'description' => $dto->seo_description,
            ], $oldImagePath));

            /** @var Post|null $freshPost */
            $freshPost = $post->fresh(['category', 'tags']);

            return $freshPost;
        });

        return $savedPost;
    }

    /**
     * Sênior: Processa as tags enviadas, criando novas se necessário e respeitando limites do plano.
     */
    private function processTags(User $user, array $rawTags): array
    {
        $tagModule = Module::where('slug', ModuleEnum::TAGS)->first();
        
        $allowCustom = (bool) ($tagModule?->getSetting('allow_custom_tags.' . $user->getPlanSlug()) ?? false);
        $maxTags = (int) ($tagModule?->getSetting('max_tags_per_post.' . $user->getPlanSlug()) ?? 5);

        // Limita a quantidade de tags logo no início
        $rawTags = array_slice($rawTags, 0, $maxTags);

        $tagIds = [];

        foreach ($rawTags as $tag) {
            // Se for numérico, assume que é um ID de tag existente
            if (is_numeric($tag)) {
                $tagIds[] = (int) $tag;
                continue;
            }

            // Se for string e o plano permitir tags customizadas, cria ou recupera
            if (is_string($tag) && $allowCustom) {
                $slug = Str::slug($tag);
                
                $newTag = Tag::query()->firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $tag,
                        'user_id' => $user->id,
                    ]
                );

                $tagIds[] = $newTag->id;
            }
        }

        return array_unique($tagIds);
    }
}
