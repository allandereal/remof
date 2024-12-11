<?php

namespace App\Jobs;

use App\Models\Transfer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Process;

class CopyFile implements ShouldQueue
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
     * Execute the job.
     */
    public function handle(): void
    {
        $this->transfer->update([
            'started_at' => now(),
        ]);

        $result = Process::forever()->run($this->transfer->buildScpCommand());

        $this->transfer->update([
            'status' => $result->successful() ? 'completed' : 'failed',
            'completed_at' => now(),
            'metadata' => [
                'output' => $result->output(),
                'exitCode' => $result->exitCode(),
                'error' => $result->errorOutput(),
            ],
        ]);
    }
}
