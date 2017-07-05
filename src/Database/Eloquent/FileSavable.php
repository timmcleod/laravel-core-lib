<?php

namespace TimMcLeod\LaravelCoreLib\Database\Eloquent;

use Crypt;
use Exception;
use Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait FileSavable
{
    /**
     * Sets the file attributes for this model and performs the actual save of the file.
     *
     * No extra fields are saved by default, but a model can "opt-in" to saving
     * extra fields by adding the key/value to the $savableFields property.
     *
     * protected $savableFields = [
     *     'size'                  => 'size_column_name',
     *     'mime_type'             => 'mime_type_column_name',
     *     'client_original_name'  => 'filename',
     * ];
     *
     * @param UploadedFile $file
     * @param bool         $saveToCloud
     * @param bool         $saveToDisk
     */
    public function saveWithFile(UploadedFile $file, $saveToCloud = false, $saveToDisk = true)
    {
        if ($this->shouldSaveField('size'))
        {
            $this->{$this->getFieldColumnName('size')} = $file->getSize();
        }

        if ($this->shouldSaveField('mime_type'))
        {
            $this->{$this->getFieldColumnName('mime_type')} = $file->getMimeType();
        }

        if ($this->shouldSaveField('client_original_name'))
        {
            $this->{$this->getFieldColumnName('client_original_name')} = $file->getClientOriginalName();
        }

        // Save the model if it doesn't already have an ID.
        if (empty($this->id)) $this->save();

        if ($saveToDisk || $saveToCloud)
        {
            $contents = $this->usingEncryption() ? Crypt::encrypt(file_get_contents($file)) : file_get_contents($file);

            if ($saveToDisk) $this->saveToDisk($contents);

            if ($saveToCloud) $this->saveToCloud($contents);
        }

        $this->save();
    }

    /**
     * @return null|string
     */
    public function getFileContents()
    {
        $contents = null;

        // Try to pull file from disk first.
        if ($this->existsOnDisk()) $contents = Storage::disk()->get($this->getStoragePath(true));

        // If it's still empty, try pulling from cloud instead.
        if (is_null($contents) && $this->existsInCloud()) $contents = Storage::cloud()->get($this->getStoragePath(true));

        // Decrypt if needed.
        if (!is_null($contents) && $this->usingEncryption())
        {
            // If decryption fails, we'll assume the file isn't
            // encrypted on disk and we'll just skip decryption.
            try {$contents = Crypt::decrypt($contents);}
            catch (Exception $e) {}
        }

        return $contents;
    }

    /**
     * @return string
     */
    public function getFileMimeType()
    {
        return $this->{$this->getFieldColumnName('mime_type')};
    }

    /**
     * @return string
     */
    public function getFileSize()
    {
        return $this->{$this->getFieldColumnName('size')};
    }

    /**
     * @return string
     */
    public function getFileClientOriginalName()
    {
        return $this->{$this->getFieldColumnName('client_original_name')};
    }

    /**
     * The path in which the file will be saved. The same path is used for
     * cloud storage and for local disk storage. By default, the path
     * uses the year and month from the created_at column for the
     * base path, and it uses the model's ID as the filename.
     *
     * @param bool $includeFilename
     * @return string
     */
    public function getStoragePath($includeFilename = false)
    {
        $classDir = snake_case(array_last(explode('\\', get_class($this))));
        $year = $this->created_at->format('Y');
        $month = $this->created_at->format('m');

        $path = "uploads/$classDir/$year/$month";

        if ($includeFilename) $path .= '/' . $this->getStoragePathFilename();

        return $path;
    }

    /**
     * The name of the file as it is stored after upload.
     *
     * @return string
     */
    public function getStoragePathFilename()
    {
        return "$this->id";
    }

    /**
     * Save the given contents to disk.
     *
     * @param $content
     * @return bool
     */
    public function saveToDisk($content)
    {
        return Storage::disk()->put($this->getStoragePath(true), $content);
    }

    /**
     * Save the given contents to the cloud.
     *
     * @param $content
     * @return bool
     */
    public function saveToCloud($content)
    {
        return Storage::cloud()->put($this->getStoragePath(true), $content);
    }

    /**
     * Delete the file from the local disk.
     *
     * @return bool
     */
    public function deleteFromDisk()
    {
        return Storage::disk()->delete($this->getStoragePath(true));
    }

    /**
     * Delete the file from the cloud.
     *
     * @return bool
     */
    public function deleteFromCloud()
    {
        return Storage::cloud()->delete($this->getStoragePath(true));
    }

    /**
     * Delete from cloud and disk.
     */
    public function deleteFile()
    {
        if ($this->existsInCloud()) $this->deleteFromCloud();
        if ($this->existsOnDisk()) $this->deleteFromDisk();
    }

    /**
     * Gets the contents of the file stored in the cloud and saves it locally.
     *
     * @return bool
     */
    public function copyFromCloudToDisk()
    {
        return $this->saveToDisk(Storage::cloud()->get($this->getStoragePath(true)));
    }

    /**
     * Gets the contents of the file stored on the disk and saves it to the cloud.
     *
     * @return bool
     */
    public function copyFromDiskToCloud()
    {
        return $this->saveToCloud(Storage::disk()->get($this->getStoragePath(true)));
    }

    /**
     * @return bool
     */
    public function existsOnDisk()
    {
        return Storage::disk()->exists($this->getStoragePath(true));
    }

    /**
     * @return bool
     */
    public function existsInCloud()
    {
        return Storage::cloud()->exists($this->getStoragePath(true));
    }

    /**
     * @return bool
     */
    public function existsAnywhere()
    {
        return $this->existsOnDisk() || $this->existsInCloud();
    }

    /**
     * @return bool
     */
    public function existsNowhere()
    {
        return !$this->existsAnywhere();
    }

    /**
     * @return bool
     */
    public function isPdf()
    {
        return strtolower($this->mime_type ?? '') == 'application/pdf';
    }

    /**
     * @return bool
     */
    public function isPng()
    {
        return strtolower($this->mime_type ?? '') == 'image/png';
    }

    /**
     * Files are not encrypted when they are saved by default, but a model can
     * "opt-in" to encryption of the file that is stored on disk or in the
     * cloud by setting the model property called $encryptFile to true.
     *
     * @return bool
     */
    public function usingEncryption()
    {
        return property_exists(static::class, 'encryptFile') && $this->encryptFile;
    }

    /**
     * No extra fields are saved by default, but a model can "opt-in" to saving
     * extra fields by adding the key/value to the $savableFields property.
     *
     * protected $savableFields = [
     *     'size'                  => 'size_column_name',
     *     'mime_type'             => 'mime_type_column_name',
     *     'client_original_name'  => 'filename',
     * ];
     *
     * @param string $field
     * @return bool
     */
    protected function shouldSaveField($field)
    {
        if (!property_exists(static::class, 'savableFields')) return false;

        return array_key_exists($field, $this->savableFields);
    }

    /**
     * Returns the name of the database column in which the given field should be saved.
     * This should be defined in the $savableFields property on the model.
     *
     * protected $savableFields = [
     *     'size'                  => 'size_column_name',
     *     'mime_type'             => 'mime_type_column_name',
     *     'client_original_name'  => 'filename',
     * ];
     *
     * @param $field
     * @return string
     */
    protected function getFieldColumnName($field)
    {
        return $this->savableFields[$field];
    }
}