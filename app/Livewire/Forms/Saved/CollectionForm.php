<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Saved;

use App\Actions\Saved\CreateCollectionAction;
use App\Actions\Saved\UpdateCollectionAction;
use App\DTOs\CollectionData;
use App\Models\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class CollectionForm extends Form
{
    public ?Collection $collection = null;

    public string $name = '';

    public ?string $slug = '';

    public ?string $description = '';

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:50',
                Rule::unique('collections', 'name')
                    ->where('user_id', auth()->id())
                    ->when($this->collection, fn ($q) => $q->ignore($this->collection->id)),
            ],
            'slug' => [
                'required',
                'string',
                'min:3',
                Rule::unique('collections', 'slug')
                    ->where('user_id', auth()->id())
                    ->when($this->collection, fn ($q) => $q->ignore($this->collection->id)),
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function setCollection(Collection $collection): void
    {
        $this->collection = $collection;
        $this->name = $collection->name;
        $this->slug = $collection->slug;
        $this->description = $collection->description;
    }

    public function store(): void
    {
        $this->validate();

        app(CreateCollectionAction::class)
            ->exec(
                user: auth()->user(),
                data: new CollectionData(
                    name: $this->name,
                    slug: $this->slug,
                    description: $this->description,
                ),
            );

        $this->reset();
    }

    public function update(): void
    {
        $this->validate();

        app(UpdateCollectionAction::class)->exec(
            collection: $this->collection,
            data: new CollectionData(
                name: $this->name,
                slug: $this->slug,
                description: $this->description,
            ),
        );

        $this->reset(['name', 'slug', 'description', 'collection']);
    }
}
