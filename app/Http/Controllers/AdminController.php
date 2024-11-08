<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SittingRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::with(['sitterProfile', 'petProfiles'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $sittingRequests = SittingRequest::with(['owner', 'sitterProfile'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Admin/Dashboard', [
            'users' => $users,
            'sittingRequests' => $sittingRequests
        ]);
    }

    public function toggleUserBlock(User $user)
    {
        $user->is_blocked = !$user->is_blocked;
        $user->save();

        return back()->with('success', 
            $user->is_blocked ? 'Gebruiker is geblokkeerd.' : 'Gebruiker is gedeblokkeerd.'
        );
    }

    public function deleteSittingRequest(SittingRequest $sittingRequest)
    {
        $sittingRequest->delete();

        return back()->with('success', 'Oppasaanvraag is verwijderd.');
    }
}