<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Dashboard;

use App\Actions\Admin\SaveCategoryAction;
use App\DTOs\SaveCategoryData;
use App\Models\PostCategory;
use Livewire\Form;

class CategoryForm extends Form
{
    public ?int $editingCategoryId = null;

    public string $name = '';

    public string $slug = '';

    public ?string $description = '';

    public function setCategory(PostCategory $category): void
    {
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = $category->description;
    }

    public function rules(): array
    {
        $userId = auth()->id();
        $id = $this->editingCategoryId;

        return [
            'name' => [
                'required', 'min:3', 'max:50',
                "unique:post_categories,name,{$id},id,user_id,{$userId}",
            ],
            'slug' => [
                'nullable', 'string', 'alpha_dash', 'max:60',
                "unique:post_categories,slug,{$id},id,user_id,{$userId}",
            ],
            'description' => ['nullable', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O campo nome é obrigatório!',
        ];
    }

    public function save()
    {
        $this->validate();

        $category = $this->editingCategoryId
            ? PostCategory::query()
                ->where('user_id', auth()->id())
                ->find($this->editingCategoryId)
            : null;

        return app(SaveCategoryAction::class)
            ->exec(
                data: new SaveCategoryData(
                    user_id: auth()->id(),
                    name: $this->name,
                    slug: $this->slug,
                    description: $this->description,
                ),
                category: $category,
            );
    }
}
