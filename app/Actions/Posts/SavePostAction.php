<?php

declare(strict_types=1);

namespace App\Actions\Posts;

use App\DTOs\SavePostData;
use App\Enums\PostStatusEnum;
use App\Events\Posts\PostSaved;
use App\Models\Post;
use App\Models\PostCategory;
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
            $isPublishing = $dto->status === PostStatusEnum::PUBLISHED && (!$post || $post->status !== PostStatusEnum::PUBLISHED);
            $isScheduling = $dto->status === PostStatusEnum::SCHEDULED;

            // Sênior: Validação e Processamento de Categoria (Find or Create)
            $categoryId = $this->processCategory($user, $dto->category_id);

            // Sênior: Sanitização de Segurança contra XSS
            $sanitizedContent = Purifier::clean($dto->content, 'post_content');

            // Sênior: Gera resumo automático se estiver publicando e estiver vazio
            $excerpt = $dto->excerpt;
            if (($dto->status === PostStatusEnum::PUBLISHED || $dto->status === PostStatusEnum::SCHEDULED) && empty(trim((string) $excerpt))) {
                // Para resumos automáticos, usamos texto puro derivado do conteúdo já limpo
                $sanitizedExcerpt = Str::limit(strip_tags($sanitizedContent), 160);
            } else {
                $sanitizedExcerpt = $excerpt ? Purifier::clean($excerpt) : null;
            }

            // Removemos tags e dados de SEO para processar separadamente/background
            $data = collect($dto->toArray())->except(['tags', 'seo_title', 'seo_description', 'category_id', 'published_at'])->toArray();
            $data['content'] = $sanitizedContent;
            $data['excerpt'] = $sanitizedExcerpt;
            $data['category_id'] = $categoryId;

            // Sênior: Define a data de publicação/agendamento
            if ($isPublishing) {
                $data['published_at'] = $dto->published_at ?? now();
            } elseif ($isScheduling) {
                $data['published_at'] = $dto->published_at;
            }

            if ($post) {
                $post->update($data);
            } else {
                /** @var Post $post */
                $post = $user->posts()->create($data);
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
     * Sênior: Processa a categoria, criando se for um novo nome.
     */
    private function processCategory(User $user, int|string|null $category): ?int
    {
        if (!$category) {
            return null;
        }

        if (is_numeric($category)) {
            $existing = PostCategory::find($category);

            if ($existing && $existing->user_id !== null && $existing->user_id !== $user->id) {
                throw new Exception('A categoria selecionada é inválida.');
            }

            return (int) $category;
        }

        if (is_string($category)) {
            $slug = Str::slug($category);

            $newCategory = PostCategory::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $category,
                    'user_id' => $user->id,
                ],
            );

            return $newCategory->id;
        }

        return null;
    }

    /**
     * Sênior: Processa as tags enviadas, criando novas se necessário.
     */
    private function processTags(User $user, array $rawTags): array
    {
        $allowCustom = true;
        $maxTags = 25;

        // Limita a quantidade de tags logo no início
        $rawTags = array_slice($rawTags, 0, $maxTags);

        $tagIds = [];

        foreach ($rawTags as $tag) {
            // Se for numérico, assume que é um ID de tag existente
            if (is_numeric($tag)) {
                $tagIds[] = (int) $tag;
                continue;
            }

            // Se for string e o módulo permitir tags customizadas, cria ou recupera
            if (is_string($tag) && $allowCustom) {
                $slug = Str::slug($tag);

                $newTag = Tag::query()->firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $tag,
                        'user_id' => $user->id,
                    ],
                );

                $tagIds[] = $newTag->id;
            }
        }

        return array_unique($tagIds);
    }
}
