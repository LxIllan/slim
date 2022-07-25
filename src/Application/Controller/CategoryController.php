<?php

declare(strict_types=1);

namespace App\Application\Controller;

use App\Application\Model\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryController
{
    public static function create(string $category): Category
    {
        return Category::create(['category' => $category]);
    }

    public static function getAll(): Collection
    {
        return Category::all();
    }

    public static function get(int $id): Category
    {
        return Category::findOrFail($id);
    }

    public static function update(int $id, string $newCategory): Category
    {
        $category = Category::findOrFail($id);
        $category->category = $newCategory;
        $category->save();
        return $category;
    }
}
