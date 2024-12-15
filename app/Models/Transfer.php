<?php

namespace App\Models;

use App\Jobs\CopyFile;
use App\Jobs\CreateDirectory;
use App\Jobs\SplitDirectory;
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

    public function getChildPath(Transferable $transferable): string
    {
        return $this->path . (str_ends_with($this->path, '/') ? '' : '/') . $transferable->getLastPathPart();
    }

    public static function getFolderContents(string $path): array
    {
        $path = escapeshellarg($path);
        $contents = explode( "\n", Process::run(
            "find " . $path
            .' -maxdepth 1 ! -path ' . $path
        )->output()) ?? [];

        return array_filter($contents, fn($item) => $item !== '');
    }

    protected static function booted(): void
    {
        static::created(function (Transfer $transfer) {
            $transfer->load('transferable');

            if ($transfer->transferable->isDirectory()){
                SplitDirectory::dispatch($transfer)->onQueue('splitter');
            } else {
                CopyFile::dispatch($transfer)->onQueue('file');
            }
        });
    }

    public function retry(): void
    {
        if ($this->transferable->isDirectory()){
            CreateDirectory::dispatch($this)->onQueue('directory');
        } else {
            CopyFile::dispatch($this)->onQueue('file');
        }
    }

    public function buildScpCommand(): string
    {
        $isDirectory = $this->transferable->isDirectory();

        $parts = [
            'scp',
            //...($isDirectory ? ['-r'] : []), //This isn't working on URE external dist
            escapeshellarg($this->transferable->path),
            escapeshellarg($this->path ? $this->server->hostname.':'.$this->path : $this->server->hostname)
        ];

        return implode(' ', $parts);
    }

    public function buildMkdirCommand(): string
    {
        $creationCommand = "\"mkdir -p {$this->path}\"";

        if ($this->server->isWindows()){
            $dirPath = '\\"'.str_replace('/', "\\\\", ltrim($this->path, '/')).'\\"';
            $creationCommand = '"if not exist '.$dirPath.' mkdir '.$dirPath.'"';
        }

        $parts = [
            'ssh',
            $this->server->hostname,
            $creationCommand,
        ];

        return implode(' ', $parts);
    }
}
