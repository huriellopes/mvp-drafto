<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Dashboard\Posts;

use App\Actions\PostCollections\SavePostCollectionAction;
use App\DTOs\PostCollectionData;
use App\Enums\PostCollectionVisibilityEnum;
use App\Models\PostCollection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Livewire\Form;

class PostCollectionForm extends Form
{
    public ?PostCollection $collection = null;

    public string $name = '';

    public ?string $slug = '';

    public ?string $description = '';

    public string $visibility = 'private';

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:50',
                Rule::unique('post_collections', 'name')
                    ->where('user_id', auth()->id())
                    ->when($this->collection, fn ($q) => $q->ignore($this->collection->id)),
            ],
            'slug' => [
                'required',
                'string',
                'min:3',
                Rule::unique('post_collections', 'slug')
                    ->where('user_id', auth()->id())
                    ->when($this->collection, fn ($q) => $q->ignore($this->collection->id)),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'visibility' => ['required', new Enum(PostCollectionVisibilityEnum::class)],
        ];
    }

    public function setCollection(PostCollection $collection): void
    {
        $this->collection = $collection;
        $this->name = $collection->name;
        $this->slug = $collection->slug;
        $this->description = $collection->description;
        $this->visibility = $collection->visibility->value;
    }

    public function store(): PostCollection
    {
        $this->validate();

        $collection = resolve(SavePostCollectionAction::class)->exec(
            user: auth()->user(),
            data: $this->toData(),
        );

        $this->reset();

        return $collection;
    }

    public function update(): PostCollection
    {
        $this->validate();

        $collection = resolve(SavePostCollectionAction::class)->exec(
            user: auth()->user(),
            data: $this->toData(),
            collection: $this->collection,
        );

        $this->reset(['name', 'slug', 'description', 'visibility', 'collection']);

        return $collection;
    }

    private function toData(): PostCollectionData
    {
        return new PostCollectionData(
            name: $this->name,
            slug: $this->slug ?: null,
            description: $this->description,
            visibility: PostCollectionVisibilityEnum::from($this->visibility),
        );
    }
}
