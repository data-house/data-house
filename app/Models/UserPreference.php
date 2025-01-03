<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'setting', 'value',
    ];


    public function hasValue(mixed $value): bool
    {
        return $this->value === $value;
    }
    protected function casts(): array
    {
        return [
            'setting' => Preference::class,
        ];
    }
}
