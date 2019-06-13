<?php

namespace App\Libs\Upload;


use Illuminate\Http\UploadedFile;

class UploadFactory
{
    public static function putFile(UploadedFile $file)
    {
        return app(Upload::class)->setFile($file);
    }

    public static function setDisk($disk)
    {
        return app(Upload::class)->setDisk($disk);
    }

    public static function mediaUrl($path, $routePrefix)
    {
        return app(Upload::class)->mediaUrl($path, $routePrefix);
    }
}