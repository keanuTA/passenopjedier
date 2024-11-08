<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SitterProfile;
use Illuminate\Auth\Access\HandlesAuthorization;

class SitterProfilePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SitterProfile $sitterProfile): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SitterProfile $sitterProfile): bool
    {
        return $user->id === $sitterProfile->user_id;
    }

    /**
     * Determine whether the user can edit the model.
     */
    public function edit(User $user, SitterProfile $sitterProfile): bool
    {
        return $user->id === $sitterProfile->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SitterProfile $sitterProfile): bool
    {
        return $user->id === $sitterProfile->user_id;
    }
}