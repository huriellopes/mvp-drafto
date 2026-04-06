<?php

namespace App\Livewire\Forms\Dashboard;

use App\Models\Post;
use Illuminate\Support\Str;
use Livewire\Form;
use Livewire\Attributes\Validate;

class PostForm extends Form
{
    public ?Post $post = null;

    #[Validate('required|min:5|max:255')]
    public string $title = '';

    #[Validate('required|exists:post_categories,id')]
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

    public function setPost(Post $post)
    {
        $this->post = $post;
        $this->title = $post->title;
        $this->slug = $post->slug; // Adicionado
        $this->category_id = $post->category_id;
        $this->content = $post->content;
        $this->excerpt = $post->excerpt ?? '';
        $this->type = $post->type->value;
        $this->visibility = $post->visibility->value;
        $this->comments_enabled = $post->comments_enabled;
    }

    public function getAttributes(): array
    {
        $dto = new \App\DTOs\SavePostDTO(
            title: $this->title,
            slug: $this->slug ?: Str::slug($this->title),
            category_id: (int) $this->category_id,
            content: $this->content,
            excerpt: $this->excerpt,
            type: $this->type,
            visibility: $this->visibility,
            comments_enabled: $this->comments_enabled,
            reading_time: (int) ceil(str_word_count(strip_tags($this->content)) / 200),
        );

        return $dto->toArray();
    }
}
