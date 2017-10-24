<?php

namespace App\Transformers;

use App\Category;
use function GuzzleHttp\Psr7\str;
use League\Fractal\TransformerAbstract;

class CategoryTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @param Category $category
     * @return array
     */
    public function transform(Category $category)
    {
        return [
            'identifier' => (int)$category->id,
            'title' => (string)$category->name,
            'details' => (string)$category->description,
            'creationDate' => (string)$category->created_at,
            'lastChange' => (string)$category->updated_at,
            'deleteDate' => isset($category->delete_at) ? (string) $category->delete_at : null,
        ];
    }

    public static function originalAttribute($index)
    {
        $attributes = [
            'identifier' => 'id',
            'title' => 'name',
            'details' => 'description',
            'creationDate' => 'created_at',
            'lastChange' => 'updated_at',
            'deleteDate' => 'delete_at',
        ];

        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
