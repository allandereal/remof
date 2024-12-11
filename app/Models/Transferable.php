<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transferable extends Model
{
    protected $guarded = [];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class);
    }

    public function isDirectory(): bool
    {
        return $this->type === 'Directory' || is_dir($this->path);
    }

    public function getDirPath(): string
    {
        $paths = [];
        $transferable = $this;
        FindFolders:

        $folders =  array_filter(explode('/', $transferable->path));
        array_unshift($paths, end($folders));

        if (filled($transferable->transferable_id)){
            $transferable = self::find($transferable->transferable_id);
            goto FindFolders;
        }

        return implode('/', $paths);
    }

    public static function getHashCommand($path): string
    {
        return 'b2sum "'.$path.'" | awk \'{ print $1 }\'';
    }
}
