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

    public static function delete($path)
    {
        return app(Upload::class)->delete($path);
    }

    public static function putBase64Str($str)
    {
        return app(Upload::class)->setBase64($str);
    }
}