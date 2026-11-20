<?php

namespace App\Models;

use Database\Factories\GroupTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class GroupType extends Model
{
    /** @use HasFactory<GroupTypeFactory> */
    use HasFactory;

    /**
     * Grupos concretos de este tipo.
     *
     * @return HasMany<Group, $this>
     */
    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }
}
