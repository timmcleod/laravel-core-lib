<?php

namespace TimMcLeod\LaravelCoreLib\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Storage;

class CopyLocalFileToCloud extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var string */
    protected $path;

    /** @var bool */
    protected $deleteAfterCopy;

    /**
     * CopyLocalFileToCloud constructor.
     *
     * @param string $path
     * @param bool   $deleteAfterCopy
     */
    public function __construct($path, $deleteAfterCopy = false)
    {
        $this->path = $path;
        $this->deleteAfterCopy = $deleteAfterCopy;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Storage::cloud()->put($this->path, Storage::disk()->get($this->path));

        if ($this->deleteAfterCopy)
        {
            Storage::disk()->delete($this->path);
        }
    }
}
