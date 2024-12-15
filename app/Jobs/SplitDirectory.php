<?php

namespace App\Jobs;

use App\Enums\TransferableType;
use App\Models\Transfer;
use App\Models\Transferable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SplitDirectory implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Transfer $transfer)
    {
        //
    }

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 10;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        CreateDirectory::dispatch($this->transfer)->onQueue('directory');

        foreach (Transfer::getFolderContents($this->transfer->from_path) as $path){
            $isDirectory = is_dir($path);

            Transfer::create([
                'from_server_id' => $this->transfer->from_server_id,
                'to_server_id' => $this->transfer->to_server_id,
                'transfer_id' => $this->transfer->id,
                'from_path' => $path,
                'to_path' => $isDirectory ? $this->transfer->getChildPath($path) : $this->transfer->to_path,
                'type' => $isDirectory ? TransferableType::DIRECTORY->value : TransferableType::FILE->value,
            ]);
        }
    }
}
