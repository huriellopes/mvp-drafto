<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Dashboard;

use App\DTOs\SavePostData;
use App\Enums\PostStatusEnum;
use App\Enums\PostTypeEnum;
use App\Enums\PostVisibilityEnum;
use App\Models\Post;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Form;

class PostForm extends Form
{
    public ?Post $post = null;

    #[Validate('required|min:5|max:255')]
    public string $title = '';

    public ?int $category_id = null;

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

    // SEO Fields
    public bool $seo_enabled = true;

    public ?string $seo_title = null;

    public ?string $seo_description = null;

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'exists:post_categories,id',
                function ($attribute, $value, $fail) {
                    $category = \App\Models\PostCategory::find($value);
                    if ($category && $category->user_id !== null && $category->user_id !== auth()->id()) {
                        $fail('A categoria selecionada é inválida.');
                    }
                },
            ],
        ];
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

        // Carrega dados de SEO se existirem
        $this->seo_title = $post->seo?->title;
        $this->seo_description = $post->seo?->description;
    }

    public function toDTO(?string $coverImagePath = null, PostStatusEnum $status = PostStatusEnum::DRAFT): SavePostData
    {
        return new SavePostData(
            title: $this->title,
            slug: $this->slug ?: Str::slug($this->title),
            category_id: (int) $this->category_id,
            content: $this->content,
            excerpt: $this->excerpt,
            tags: $this->tags,
            type: PostTypeEnum::from($this->type),
            visibility: PostVisibilityEnum::from($this->visibility),
            status: $status,
            comments_enabled: $this->comments_enabled,
            seo_enabled: $this->seo_enabled,
            seo_title: $this->seo_title,
            seo_description: $this->seo_description,
            cover_image_path: $coverImagePath ?? ($this->post?->cover_image_path),
        );
    }
}
