<?php

namespace App\Libs\Traits;

use Illuminate\Http\Request;
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

    public function requestIsEmpty(Request $request, array $needParam = [])
    {
        $bool = false;
        $all = $request->all();
        if (sizeof($all)) {
            foreach ($all as $key => $val) {
                if (sizeof($needParam) && in_array($key, $needParam) && empty($val)) {
                    $bool = true;
                }
            }
        } else {
            $bool = true;
        }
        return $bool;
    }
}