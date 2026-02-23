<?php

namespace App\Policies;

use App\Models\Ingrediente;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class IngredientePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ingrediente $ingrediente): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        // El controlador debe verificar si el usuario es dueño de la receta.
        return true;
    }

    public function update(User $user, Ingrediente $ingrediente): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $user->id === $ingrediente->receta->user_id;
    }

    public function delete(User $user, Ingrediente $ingrediente): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $user->id === $ingrediente->receta->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Ingrediente $ingrediente): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ingrediente $ingrediente): bool
    {
        return false;
    }
}
