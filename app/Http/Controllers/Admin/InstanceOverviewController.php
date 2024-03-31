<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InstanceOverviewController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        Gate::authorize('admin-area');


        return view('admin.dashboard', [
            'total_users' => User::count(),
            'total_projects' => Project::count(),
            'total_documents' => Document::count(),
        ]);
    }
}
