<?php

namespace App\Models;

use App\Jobs\CopyFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

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

    public function getFullPath(): string
    {
        if ($this->transferable->isDirectory()){
            return $this->path.(str_ends_with($this->path, '/') ? '' : '/').$this->transferable->getDirPath();
        }

        return $this->path;
    }

    protected static function booted(): void
    {
        static::created(function (Transfer $transfer) {
            $transfer->load('transferable');
            if ($transfer->transferable->isDirectory() && blank($transfer->transferable->transferable_id)){
                foreach (glob($transfer->transferable->path.'/*') ?: [] as $path){
                    $transferable = $transfer->server->transferables()->create([
                        'transferable_id' => $transfer->transferable_id,
                        'path' => $path,
                        'type' => is_dir($path) ? 'Directory' : 'File',
                        'hash' => Process::run(Transferable::getHashCommand(path: $path))->output(),
                    ]);

                    $transferable->transfers()->create([
                        'path' => $transfer->getFullPath(),
                        'server_id' => $transfer->server_id,
                    ]);
                }
            } else {
                CopyFile::dispatch($transfer);
            }
        });
    }

    public function buildScpCommand(): string
    {
        $parts = [
            'scp',
            ...($this->transferable->isDirectory() ? ['-r'] : []),
            $this->transferable->path,
            ...($this->path ? [$this->server->hostname.':'.$this->path] : [$this->server->hostname])
        ];

        return implode(' ', $parts);
    }
}
