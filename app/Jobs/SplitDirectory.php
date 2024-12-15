<?php

namespace App\Jobs;

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
    public int $tries = 5;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 18000; //5hrs

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->transfer->load('transferable');

        CreateDirectory::dispatch($this->transfer)->onQueue('directory');

        foreach (Transfer::getFolderContents($this->transfer->transferable->path) as $path){
            $transferable = Transferable::create([
                'server_id' => $this->transfer->transferable->server_id,
                'transferable_id' => $this->transfer->transferable_id,
                'path' => $path,
                'type' => is_dir($path) ? 'Directory' : 'File',
            ]);

            $transferable->transfers()->create([
                'path' => $transferable->isDirectory() ? $this->transfer->getChildPath($transferable) : $this->transfer->path,
                'server_id' => $this->transfer->server_id,
            ]);
        }
    }
}
