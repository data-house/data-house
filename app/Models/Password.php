<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Password extends Model
{
    use HasFactory;

    use MassPrunable;
    
    protected $fillable = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        $historicalAmount = config('auth.password_validation.historical_password_amount');

        if(blank($historicalAmount) || (int) $historicalAmount < 1){
            return static::query();
        }

        return static::query()
            ->whereIn('id', function ($query) use ($historicalAmount) {
                $query->select('id')
                    ->from(function ($subQuery) {
                        $subQuery->select('id', DB::raw('row_number() over (partition by user_id order by created_at desc) as row_num'))
                            ->from($this->getTable());
                    }, 'numbered_rows')
                    ->where('row_num', '>', (int) $historicalAmount);
            })
            ->orderBy('created_at', 'asc');
    }
}

