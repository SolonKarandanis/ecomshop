<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class RoleRepository
{

    public function modelQuery(): Builder| Role{
        return Role::query();
    }

    public function getAllRoles(): Collection{
        return $this->modelQuery()->get();
    }

    public function getRoleById(int $id): Role{
        return $this->modelQuery()->find($id);
    }

    public function getRoleByName(string $name): Role{
        return $this->modelQuery()->where("name", $name)->first();
    }
}
