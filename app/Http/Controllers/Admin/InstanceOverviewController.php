<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentSummary;
use App\Models\Note;
use App\Models\Project;
use App\Models\Question;
use App\Models\Star;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class InstanceOverviewController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        Gate::authorize('admin-area');


        $statistics = [
            __('Active sessions') => DB::table('sessions')->count(),
            __('Active user sessions') => DB::table('sessions')->whereNotNull('user_id')->count(),
            __('Teams') => Team::count(),
            __('Stars') => Star::count(),
            __('Notes') => Note::count(),
            __('Summaries') => DocumentSummary::count(),
            __('Questions') => Question::count(),
        ];


        return view('admin.dashboard', [
            'total_users' => User::count(),
            'total_projects' => Project::count(),
            'total_documents' => Document::count(),
            'statistics' => $statistics,
        ]);
    }
}
