<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;

class BlogPostPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->allowed($user, 'blog.view');
    }

    public function view(User $user, BlogPost $post): bool
    {
        return $this->allowed($user, 'blog.view');
    }

    public function create(User $user): bool
    {
        return $this->allowed($user, 'blog.create');
    }

    public function update(User $user, BlogPost $post): bool
    {
        return $this->allowed($user, 'blog.edit');
    }

    public function delete(User $user, BlogPost $post): bool
    {
        return $this->allowed($user, 'blog.delete');
    }

    public function restore(User $user, BlogPost $post): bool
    {
        return $this->allowed($user, 'blog.delete');
    }

    public function forceDelete(User $user, BlogPost $post): bool
    {
        return $this->allowed($user, 'blog.delete');
    }

    private function allowed(User $user, string $permission): bool
    {
        return $user->hasRole('super-admin')
            || $user->hasRole('admin')
            || $user->hasPermission($permission);
    }
}
