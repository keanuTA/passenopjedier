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
        \Log::info('Admin index accessed', [
            'user_id' => auth()->id(),
            'is_admin' => auth()->user()->is_admin
        ]);

        $users = User::with(['sitterProfile', 'petProfiles'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $sittingRequests = SittingRequest::with([
                'user', // eigenaar
                'sitterProfile.user', // oppas
                'petProfile' // huisdier info
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Admin/Dashboard', [
            'users' => $users,
            'sittingRequests' => $sittingRequests,
            'can' => [
                'manage_users' => auth()->user()->is_admin
            ]
        ]);
    }

    public function toggleUserBlock(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Je kunt jezelf niet blokkeren.');
        }

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