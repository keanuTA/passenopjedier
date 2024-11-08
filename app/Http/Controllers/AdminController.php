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
        $users = User::all();
        $sittingRequests = SittingRequest::with(['petProfile', 'user', 'sitterProfile.user'])->get();

        return Inertia::render('Admin/Dashboard', [
            'users' => $users,
            'sittingRequests' => $sittingRequests
        ]);
    }

    public function updateUserStatus(Request $request, User $user)
    {
        $validated = $request->validate([
            'action' => 'required|in:block,unblock'
        ]);

        $user->status = $validated['action'] === 'block' ? 'blocked' : 'active';
        $user->save();

        return back()->with('success', 'Gebruiker status is bijgewerkt');
    }

    public function updateRequestStatus(Request $request, SittingRequest $sittingRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:delete,complete'
        ]);

        if ($validated['status'] === 'delete') {
            $sittingRequest->delete();
            return back()->with('success', 'Oppasverzoek is verwijderd');
        }

        $sittingRequest->status = 'completed';
        $sittingRequest->save();
        return back()->with('success', 'Oppasverzoek is gemarkeerd als voltooid');
    }
    public function deleteSittingRequest(SittingRequest $sittingRequest)
    {
        $sittingRequest->delete();
        return back()->with('success', 'Oppasverzoek verwijderd.');
    }

    public function toggleUserBlock(User $user)
    {
        $user->status = $user->status === 'active' ? 'blocked' : 'active';
        $user->save();
        return back()->with('success', 'Gebruiker status bijgewerkt.');
    }
}