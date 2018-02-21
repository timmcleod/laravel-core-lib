<?php

namespace TimMcLeod\LaravelCoreLib\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Storage;

class CopyLocalFileToCloud implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

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

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return bool
     */
    public function willDeleteAfterCopy()
    {
        return $this->deleteAfterCopy;
    }
}
