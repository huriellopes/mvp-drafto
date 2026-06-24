<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Dashboard;

use App\DTOs\SavePostData;
use App\Enums\PostStatusEnum;
use App\Enums\PostTypeEnum;
use App\Enums\PostVisibilityEnum;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Form;

class PostForm extends Form
{
    public ?Post $post = null;

    #[Validate('required|min:5|max:255')]
    public string $title = '';

    public int|string|null $category_id = null;

    #[Validate('required|string|min:5')]
    public string $content = '';

    #[Validate('nullable|string|max:500')]
    public string $excerpt = '';

    #[Validate('required')]
    public string $type = 'post';

    #[Validate('required')]
    public string $visibility = 'public';

    public bool $comments_enabled = true;

    public string $slug = '';

    public array $tags = [];

    public array $collections = [];

    // SEO Fields
    public bool $seo_enabled = true;

    public ?string $seo_title = null;

    public ?string $seo_description = null;

    // Sem "after:now": ao editar um post já publicado, a data carregada é
    // passada e quebraria a validação de atualizar/publicar. A exigência de
    // data futura para agendamento é checada manualmente em schedule().
    #[Validate('nullable|date')]
    public ?string $published_at = null;

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (is_numeric($value)) {
                        $category = PostCategory::find($value);

                        if (!$category || ($category->user_id !== null && $category->user_id !== auth()->id())) {
                            $fail('A categoria selecionada é inválida.');
                        }
                    } elseif (!is_string($value) || empty(mb_trim($value))) {
                        $fail('A categoria é obrigatória.');
                    }
                },
            ],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'title' => 'título',
            'category_id' => 'categoria',
            'content' => 'conteúdo',
            'excerpt' => 'resumo',
            'type' => 'tipo de conteúdo',
            'visibility' => 'visibilidade',
        ];
    }

    /**
     * Validação simplificada para rascunhos.
     * Permite salvar apenas com o título preenchido.
     */
    public function validateForDraft(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
        ]);
    }

    public function setPost(Post $post)
    {
        $this->post = $post;
        $this->title = $post->title;
        $this->slug = $post->slug;
        $this->category_id = $post->category_id;
        $this->content = $post->content;
        $this->excerpt = $post->excerpt ?? '';
        $this->type = $post->type->value;
        $this->visibility = $post->visibility->value;
        $this->comments_enabled = $post->comments_enabled;
        $this->seo_enabled = $post->seo_enabled;
        $this->tags = $post->tags->pluck('id')->toArray();
        $this->collections = $post->collections->pluck('id')->toArray();
        $this->published_at = $post->published_at?->format('Y-m-d\TH:i');

        // Carrega dados de SEO se existirem
        $this->seo_title = $post->seo?->title;
        $this->seo_description = $post->seo?->description;
    }

    public function toDTO(?string $coverImagePath = null, PostStatusEnum $status = PostStatusEnum::DRAFT): SavePostData
    {
        return new SavePostData(
            title: $this->title,
            slug: $this->slug ?: Str::slug($this->title),
            category_id: $this->category_id ?: null,
            content: $this->content ?: '',
            excerpt: $this->excerpt,
            tags: $this->tags,
            collections: $this->collections,
            type: PostTypeEnum::from($this->type),
            visibility: PostVisibilityEnum::from($this->visibility),
            status: $status,
            comments_enabled: $this->comments_enabled,
            seo_enabled: $this->seo_enabled,
            seo_title: $this->seo_title,
            seo_description: $this->seo_description,
            cover_image_path: $coverImagePath ?? ($this->post?->cover_image_path),
            published_at: $this->published_at,
        );
    }
}
