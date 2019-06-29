<?php

namespace App\Libs\Upload;


use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class Upload
{
    protected $uploadFile;
    protected $fileName;
    protected $dataType;
    protected $ext;
    protected $path;
    protected $disk = 'media';
    protected $savePath;
    protected $saveFileName;

    public function setFile(UploadedFile $file)
    {
        $this->uploadFile = $file;
        $this->fileName = $file->getFilename();
        $this->ext = $file->getClientOriginalExtension();
        $this->path = dechex(rand(0, 15)) . dechex(rand(0, 15));
        $this->saveFileName = $file->getClientOriginalName();
        $this->dataType = $file->getClientMimeType();
        if (empty($this->ext)) {
            $exts = explode('/', $this->dataType);
            if ($exts[1]) {
                $this->ext = $exts[1];
            }
        }
        return $this;
    }

    public function setSaveFileName($name)
    {
        $this->saveFileName = $name;
        return $this;
    }

    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    public function setDisk($disk)
    {
        $this->disk = $disk;
        return $this;
    }

    public function save($hex = true)
    {
        if (empty($this->saveFileName) && empty($this->ext)) {
            return null;
        }
        $this->saveFileName = $hex ? Str::uuid()->getHex() . '.' . $this->ext : $this->saveFileName;
        $this->savePath = Storage::disk($this->disk)->putFileAs($this->path, $this->uploadFile, $this->saveFileName);
        return $this->savePath ? $this->info() : null;
    }

    public function info()
    {
        $res = [
            'fileName' => $this->fileName,
            'ext' => $this->ext,
            'savePath' => $this->savePath,
            'disk' => $this->disk,
            'size' => Storage::disk($this->disk)->size($this->savePath),
            'saveFileName' => $this->saveFileName,
            'dataType' => $this->dataType,
        ];
        return (object)$res;
    }

    public function mediaUrl($path, $type)
    {
        return Storage::disk($this->disk)->url($type . '/' . $path);
    }

    public function delete($path)
    {
        $exists = Storage::disk($this->disk)->exists($path);
        if ($exists) {
            Storage::disk($this->disk)->delete($path);
        }
    }
}