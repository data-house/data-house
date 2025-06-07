<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\QueryBuilderRequest;

class RetrievalRequest extends QueryBuilderRequest
{

    public function searchQuery(): ?string
    {
        return $this->has('s') ? $this->string('s') : null;
    }

    public function isSearch(): bool
    {
        return !empty($this->searchQuery());
    }
    
    public function sorts(): Collection
    {
        return parent::sorts();
    }

    public function filters(): Collection
    {
        $filters = parent::filters();

        $sourceFilters = $this->hasAny(['source']) ? $this->only(['source']) : ['source' => 'all-teams'];

        $teamFilters = $sourceFilters['source'] === 'current-team' ? ['team_id' => $this->user()->currentTeam->getKey()] : [];

        $starredFilters = $this->hasAny(['starred']) ? ['stars' => $this->user()->getKey()] : [];

        return $filters
            ->merge($teamFilters)
            ->merge($starredFilters)
            ->merge($sourceFilters)
            ->merge($this->only(['project_countries', 'format', 'type', 'project_region', 'project_topics', 'library_collections']));
    }

    public function hasAppliedFilters(): bool
    {
        return $this->filters()->except('source')->isNotEmpty();
    }
    
    public function appliedFiltersCount(): int
    {
        return $this->filters()->except('source')->count();
    }


    public static function fromArray(array $data): self
    {
        return (new static())->merge($data);
    }
}
