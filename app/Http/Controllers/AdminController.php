<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SittingRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function index()
    {
        try {
            $users = User::where('id', '!=', auth()->id())
                ->select('id', 'name', 'email', 'is_blocked', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            $sittingRequests = SittingRequest::with(['user', 'sitterProfile.user'])
                ->latest()
                ->get();

            return Inertia::render('Admin/Dashboard', [
                'users' => $users,
                'sittingRequests' => $sittingRequests
            ]);
        } catch (\Exception $e) {
            Log::error('Error in admin dashboard:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'error' => 'Er is een fout opgetreden bij het laden van het dashboard.'
            ]);
        }
    }

    public function toggleUserBlock(User $user)
    {
        try {
            if ($user->id === auth()->id()) {
                return back()->with('error', 'Je kunt jezelf niet blokkeren.');
            }

            // Log de huidige status
            Log::info('Toggling user block status:', [
                'user_id' => $user->id,
                'current_status' => $user->is_blocked,
                'new_status' => !$user->is_blocked
            ]);

            $user->is_blocked = !$user->is_blocked;
            $user->save();

            return back()->with('success', 
                $user->is_blocked 
                    ? 'Gebruiker is geblokkeerd.' 
                    : 'Gebruiker is gedeblokkeerd.'
            );

        } catch (\Exception $e) {
            Log::error('Error toggling user block:', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'error' => 'Er is een fout opgetreden bij het wijzigen van de gebruikersstatus.'
            ]);
        }
    }

    public function deleteSittingRequest(SittingRequest $sittingRequest)
    {
        try {
            Log::info('Deleting sitting request:', [
                'request_id' => $sittingRequest->id,
                'user_id' => $sittingRequest->user_id
            ]);

            $sittingRequest->delete();

            return back()->with('success', 'Oppasaanvraag is verwijderd.');

        } catch (\Exception $e) {
            Log::error('Error deleting sitting request:', [
                'request_id' => $sittingRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'error' => 'Er is een fout opgetreden bij het verwijderen van de oppasaanvraag.'
            ]);
        }
    }
}