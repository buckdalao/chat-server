<?php

namespace App\Libs\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait UtilTraits
{
    public function fileIsImage($path)
    {
        if (File::exists($path)) {
            $fileName = File::basename($path);
            $extension = Str::substr($fileName, strpos($fileName, '.') + 1);
            if (in_array(strtolower($extension), [
                'bmp', 'jpg', 'png', 'tif', 'gif', 'pcx', 'tga', 'exif', 'fpx', 'svg', 'psd', 'cdr', 'pcd', 'dxf', 'ufo', 'eps', 'ai', 'raw', 'wmf', 'webp'
            ])) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}