<?php

namespace App\Actions\Catalog;

use App\Models\Catalog;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateCatalog
{
    public function __invoke(Catalog $catalog, string $title, ?string $description = null, ?User $user = null): Catalog
    {
        /**
         * @var \App\Models\User
         */
        $user = $user ?? auth()->user();

        throw_unless($user->can('update', $catalog), AuthorizationException::class);

        $input = [
            'title' => $title,
            'description' => $description,
        ];

        Validator::make($input, [
            'title' => ['required',
                'string',
                'min:1',
                'max:255',
                Rule::unique((new Catalog)->getTable(), 'title')
                    ->where('visibility', Visibility::TEAM)
                    ->where('team_id', $user->currentTeam->getKey()),
            ],
            'description' => ['nullable', 'string', 'max:6000'],
        ])->validate();

        $catalog->fill([
                'title' => $title,
                'description' => $description,
            ])
            ->save();

        return $catalog;
    }
}
