<?php

namespace App\Models;

use App\Jobs\CopyFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transfer extends Model
{
    protected $guarded = [];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function transferable(): BelongsTo
    {
        return $this->belongsTo(Transferable::class);
    }

    protected static function booted(): void
    {
        static::created(function (Transfer $model) {
            CopyFile::dispatch($model);
        });
    }

    public function buildScpCommand(): string
    {
        $parts = [
            'scp',
            ...($this->transferable->isDirectory() ? ['-r'] : []),
            $this->transfer->transferable->path,
            $this->transfer->server->hostname,
            ...($this->transfer->path ? [':'.$this->transfer->path] : [])
        ];

        return implode(' ', $parts);
    }
}
