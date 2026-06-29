<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\DTOs\SaveCategoryData;
use App\Models\PostCategory;

final class SaveCategoryAction
{
    /**
     * Save or update a post category.
     */
    public function exec(SaveCategoryData $data, ?PostCategory $category = null): PostCategory
    {
        $payload = [
            'user_id' => $data->user_id,
            'name' => $data->name,
            'slug' => $data->slug ?: str($data->name)->slug()->value(),
            'description' => $data->description,
        ];

        if ($category instanceof PostCategory) {
            $category->update($payload);

            return $category->fresh();
        }

        return PostCategory::create($payload);
    }
}
