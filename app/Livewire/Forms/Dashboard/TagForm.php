<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Dashboard;

use App\Models\Tag;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Form;

class TagForm extends Form
{
    public ?int $editingTagId = null;

    #[Validate('required|min:2|max:30|unique:tags,name')]
    public string $name = '';

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'min:2',
                'max:30',
                'unique:tags,name,' . $this->editingTagId,
            ],
        ];
    }

    public function setTag(Tag $tag): void
    {
        $this->editingTagId = $tag->id;
        $this->name = $tag->name;
    }

    public function save(): Tag
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
        ];

        if ($this->editingTagId) {
            $tag = Tag::query()->where('user_id', auth()->id())->findOrFail($this->editingTagId);
            $tag->update($data);
        } else {
            $tag = Tag::query()->create(array_merge($data, [
                'user_id' => auth()->id(),
            ]));
        }

        return $tag;
    }
}
