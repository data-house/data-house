<?php

namespace App\Data;

use App\Models\Role;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class ReviewSettings extends Data
{
    public function __construct(
        public bool $questionReview = false,

        /** @var array<int, \App\Models\Role> */ 
        public array $assignableUserRoles = [Role::MANAGER],
    ) {
    }
}
