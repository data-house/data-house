<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\MeilisearchEngine;
use Meilisearch\Client;
use Meilisearch\Endpoints\Indexes;
use Tests\TestCase;
use Mockery as m;

class SearchableTest extends TestCase
{
    use RefreshDatabase;

    public function test_exact_matches_supported(): void
    {
        config(['scout.driver' => 'fake']);

        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $documentsVisibleByTeam = Document::factory()
            ->recycle($user)
            ->recycle($user->currentTeam)
            ->count(2)
            ->create();

        $documentsVisibleAllUsers = Document::factory()
            ->visibleByAnyUser()
            ->count(2)
            ->create();

        $notAccessibleDocuments = Document::factory()
            ->count(2)
            ->create();

        $visibleDocuments = $documentsVisibleByTeam->merge($documentsVisibleAllUsers);

        $query = Document::query()->visibleBy($user);

        $expectedSearchOptions = [
            'filter' => "(uploaded_by = {$user->getKey()} AND visibility = 10 OR visibility IN [30,40] OR team_id = {$user->currentTeam->getKey()} AND visibility = 20)",
        ];

        $this->prepareScoutSearchMockUsing('"exact match"', $query, $expectedSearchOptions);

        /**
         * @var \Illuminate\Pagination\LengthAwarePaginator
         */
        $paginator = Document::tenantSearch('"exact match"', [], $user)->paginateRaw();

        $this->assertSame($visibleDocuments->count(), $paginator->total());
        $this->assertSame(1, $paginator->lastPage());
        $this->assertSame(15, $paginator->perPage());
        $this->assertEquals(
            $visibleDocuments->pluck('id')->toArray(),
            collect($paginator->items()['hits'])->pluck('id')->toArray()
        );
    }

    public function test_user_rbac_filters_applied_during_search(): void
    {
        config(['scout.driver' => 'fake']);

        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $documentsVisibleByTeam = Document::factory()
            ->recycle($user)
            ->recycle($user->currentTeam)
            ->count(2)
            ->create();

        $documentsVisibleAllUsers = Document::factory()
            ->visibleByAnyUser()
            ->count(2)
            ->create();

        $notAccessibleDocuments = Document::factory()
            ->count(2)
            ->create();

        $visibleDocuments = $documentsVisibleByTeam->merge($documentsVisibleAllUsers);

        $query = Document::query()->visibleBy($user);

        $expectedSearchOptions = [
            'filter' => "(uploaded_by = {$user->getKey()} AND visibility = 10 OR visibility IN [30,40] OR team_id = {$user->currentTeam->getKey()} AND visibility = 20)",
        ];

        $this->prepareScoutSearchMockUsing('*', $query, $expectedSearchOptions);

        /**
         * @var \Illuminate\Pagination\LengthAwarePaginator
         */
        $paginator = Document::tenantSearch('*', [], $user)->paginateRaw();

        $this->assertSame($visibleDocuments->count(), $paginator->total());
        $this->assertSame(1, $paginator->lastPage());
        $this->assertSame(15, $paginator->perPage());
        $this->assertEquals(
            $visibleDocuments->pluck('id')->toArray(),
            collect($paginator->items()['hits'])->pluck('id')->toArray()
        );
    }

    public function test_user_rbac_filters_applied_during_search_with_user_defined_filters(): void
    {
        config(['scout.driver' => 'fake']);

        $user = User::factory()
            ->withPersonalTeam()
            ->manager()
            ->create();

        $documentsVisibleByTeam = Document::factory()
            ->recycle($user)
            ->recycle($user->currentTeam)
            ->count(2)
            ->create();

        $documentsVisibleAllUsers = Document::factory()
            ->visibleByAnyUser()
            ->count(2)
            ->create();

        $notAccessibleDocuments = Document::factory()
            ->count(2)
            ->create();

        $visibleDocuments = $documentsVisibleByTeam->merge($documentsVisibleAllUsers);

        $query = Document::query()->visibleBy($user);

        $expectedSearchOptions = [
            'filter' => "(uploaded_by = {$user->getKey()} AND visibility = 10 OR visibility IN [30,40] OR team_id = {$user->currentTeam->getKey()} AND visibility = 20) AND type IN [\"REPORT\"]",
        ];

        $this->prepareScoutSearchMockUsing('*', $query, $expectedSearchOptions);

        /**
         * @var \Illuminate\Pagination\LengthAwarePaginator
         */
        $paginator = Document::tenantSearch('*', ['type' => ['REPORT']], $user)->paginateRaw();

        $this->assertSame($visibleDocuments->count(), $paginator->total());
        $this->assertSame(1, $paginator->lastPage());
        $this->assertSame(15, $paginator->perPage());
        $this->assertEquals(
            $visibleDocuments->pluck('id')->toArray(),
            collect($paginator->items()['hits'])->pluck('id')->toArray()
        );
    }


    protected function prepareScoutSearchMockUsing($searchQuery, $query, $expectedSearchOptions = [])
    {
        $engine = m::mock(Client::class);
        $indexes = m::mock(Indexes::class);

        $manager = $this->app->make(EngineManager::class);
        $manager->extend('fake', function () use ($engine) {
            return new MeilisearchEngine($engine);
        });

        $hitsPerPage = 15;
        $page = 1;
        $totalPages = intval($query->count() / $hitsPerPage);

        $engine->shouldReceive('index')->with('documents')->andReturn($indexes);

        $indexes->shouldReceive('rawSearch')
            ->with($searchQuery, [
                "facets" => [
                    "id",
                    "draft",
                    "languages",
                    "mime",
                    "type",
                    'team_id',
                    'team_name',
                    'uploaded_by',
                    'visibility',
                    "published",
                    "published_at",
                    "project_countries",
                    "project_region",
                    "project_topics",
                ],
                'hitsPerPage' => $hitsPerPage,
                'page' => $page,
                ...$expectedSearchOptions,
            ])
            ->andReturn([
                'query' => $searchQuery,
                'hits' => $query->get()->transform(function ($result) {
                    return [
                        'id' => $result->getKey(),
                        'title' => $result->title,
                    ];
                })->toArray(),
                'hitsPerPage' => $hitsPerPage,
                'page' => $page,
                'totalHits' => $query->count(),
                'totalPages' => $totalPages > 0 ? $totalPages : 0,
                'processingTimeMs' => 1,
            ]);
    }
}
