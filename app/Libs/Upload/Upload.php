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
    protected $saveFileSource;

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

    public function setBase64($base64)
    {
        $base64Regexp = '/^\s*data:([a-z]+\/[a-z0-9-+.]+(;[a-z-]+=[a-z0-9-]+)?)?(;base64)?,([a-z0-9!$&\',()*+;=\-._~:@\/?%\s]*?)\s*$/i';
        if (preg_match($base64Regexp, $base64, $res)) {
            if ($res[1] && $res[4]) {
                $this->dataType = $res[1];
                $exts = explode('/', $this->dataType);
                if ($exts[1]) {
                    $this->ext = $exts[1];
                }
                $this->saveFileSource = base64_decode($res[4]);
                $this->saveFileName = Str::uuid()->getHex() . '.' . $this->ext;
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
        if ($this->saveFileSource) {
            Storage::disk($this->disk)->put($this->path . '/' . $this->saveFileName, $this->saveFileSource);
            $this->savePath = $this->path . '/' . $this->saveFileName;
        } elseif ($this->uploadFile) {
            $this->savePath = Storage::disk($this->disk)->putFileAs($this->path, $this->uploadFile, $this->saveFileName);
        }
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