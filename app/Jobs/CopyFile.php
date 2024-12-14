<?php

namespace App\Jobs;

use App\Models\Transfer;
use App\Models\Transferable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
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
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 2;

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
        $this->transfer->update([
            'started_at' => now(),
            'status' => 'started',
        ]);

        $this->transfer->load('transferable');

//        $this->transfer->transferable->update([
//            'hash' => Process::run(Transferable::getHashCommand(path: $this->transfer->transferable->path))->output()
//        ]);

        $result = Process::forever()
            ->run(
                $this->transfer->buildScpCommand(),
                function (string $type, string $output) {
                    Log::info('SCPLog: ', [$type, $output]);
//                  if ($type === Process::OUT) {
//                      // Handle standard output
//                      $this->output->write($buffer);
//
//                      // Parse and display progress if available
//                      if (preg_match('/\bETA\b|\b([0-9]+%)\b/', $buffer)) {
//                          $this->info(trim($buffer));
//                      }
//                  } else {
//                      // Handle errors
//                      $this->error($buffer);
//                  }
                }
            );

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
