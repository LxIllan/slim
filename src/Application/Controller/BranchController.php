<?php

declare(strict_types=1);

namespace App\Application\Controller;

use App\Application\Model\Branch;
use Illuminate\Database\Eloquent\Collection;

class BranchController
{
    public static function create(string $name, string $location, string $phoneNumber)
    {
        $branch = Branch::create(['name' => $name, 'location' => $location, 'phone_number' => $phoneNumber]);
        return $branch;
    }

    public static function getAll(): Collection
    {
        return Branch::all();
    }

    public static function get(int $id): Branch
    {
        return Branch::findOrFail($id);
    }
}