<?php

namespace App\Policies;

use App\Models\Comentario;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ComentarioPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Comentario $comentario): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Comentario $comentario): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $user->id === $comentario->user_id;
    }

    public function delete(User $user, Comentario $comentario): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $user->id === $comentario->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Comentario $comentario): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Comentario $comentario): bool
    {
        return false;
    }
}
