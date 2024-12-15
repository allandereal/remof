<?php

namespace App\Models;

use App\Enums\TransferableType;
use App\Enums\TransferStatus;
use App\Jobs\CopyFile;
use App\Jobs\CreateDirectory;
use App\Jobs\SplitDirectory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Process\ProcessResult;
use Illuminate\Support\Facades\Process;

class Transfer extends Model
{
    protected $guarded = [];

    public function fromServer(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'from_server_id');
    }

    public function toServer(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'to_server_id');
    }

    public function isDirectory(): bool
    {
        return $this->type === TransferableType::DIRECTORY->value || is_dir($this->from_path);
    }

    public function getChildPath($path): string
    {
        return $this->to_path . (str_ends_with($this->to_path, '/') ? '' : '/') . preg_replace("/^.*\/(.*)$/", "$1", $path);
    }

    public static function getFolderContents(string $path): array
    {
        $path = escapeshellarg($path);

        $contents = explode(
            "\n",
            Process::run("find " . $path .' -maxdepth 1 ! -path ' . $path)->output()
        ) ?? [];

        return array_filter($contents, fn($item) => $item !== '');
    }

    protected static function booted(): void
    {
        static::created(function (Transfer $transfer) {
            if ($transfer->isDirectory()){
                SplitDirectory::dispatch($transfer)->onQueue('splitter');
            } else {
                CopyFile::dispatch($transfer)->onQueue('file');
            }
        });
    }

    public function retry(): void
    {
        if ($this->isDirectory()){
            CreateDirectory::dispatch($this)->onQueue('directory');
        } else {
            CopyFile::dispatch($this)->onQueue('file');
        }
    }

    public function updateAfterUpload(ProcessResult $processResult): void
    {
        $this->update([
            'status' => $processResult->successful() ? TransferStatus::COMPLETED->value : TransferStatus::FAILED->value,
            'completed_at' => now(),
            'metadata' => [
                'response' => [
                    'output' => $processResult->output(),
                    'exitCode' => $processResult->exitCode(),
                    'error' => $processResult->errorOutput(),
                ]
            ],
        ]);
    }

    public function buildScpCommand(): string
    {
        $parts = [
            'scp',
            escapeshellarg($this->from_path),
            escapeshellarg($this->to_path ? $this->server->hostname.':'.$this->to_path : $this->server->hostname)
        ];

        return implode(' ', $parts);
    }

    public function buildMkdirCommand(): string
    {
        $creationCommand = "\"mkdir -p {$this->to_path}\"";

        if ($this->toServer->isWindows()){
            $dirPath = '\\"'.str_replace('/', "\\\\", ltrim($this->to_path, '/')).'\\"';
            $creationCommand = '"if not exist '.$dirPath.' mkdir '.$dirPath.'"';
        }

        $parts = [
            'ssh',
            $this->server->hostname,
            $creationCommand,
        ];

        return implode(' ', $parts);
    }

    public static function getHashCommand($path): string
    {
        return 'b2sum "'.$path.'" | awk \'{ print $1 }\'';
    }
}
