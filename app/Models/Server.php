<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Server extends Model
{
    protected $guarded = [];

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class);
    }

    public function transferables(): HasMany
    {
        return $this->hasMany(Transferable::class);
    }
}
