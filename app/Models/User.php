<?php

namespace App\Models;

use NeoPhp\Database\Model;

class User extends Model
{
    protected static $table = 'users';
    protected static $primaryKey = 'id';
    protected static $timestamps = true;

    // Relationships example
    public function posts(): array
    {
        return Post::where('user_id', $this->id)->get();
    }

    // Scopes
    public static function active(): array
    {
        return static::where('status', 'active')->get();
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->getAttribute('name');
    }
}
