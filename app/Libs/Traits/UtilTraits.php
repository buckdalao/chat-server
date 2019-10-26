<?php

namespace App\Libs\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait UtilTraits
{
    public function verifyFile($path, array $ext)
    {
        if (File::exists($path)) {
            $fileName = File::basename($path);
            $extension = Str::substr($fileName, strpos($fileName, '.') + 1);
            if (in_array(strtolower($extension), $ext)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getMediaPath($requestPath)
    {
        $requestPath = str_replace('media/audio/', '', $requestPath);
        return storage_path('app/media/' . $requestPath);
    }

    public function fileIsImage($path)
    {
        $ext = [
            'bmp', 'jpg', 'png', 'tif', 'gif', 'pcx', 'tga', 'exif', 'fpx', 'svg', 'psd', 'cdr', 'pcd', 'dxf', 'ufo', 'eps', 'ai', 'raw', 'wmf', 'webp'
        ];
        return $this->verifyFile($path, $ext);
    }

    public function isAudio($path)
    {
        $ext = [
            'wav', 'mp3'
        ];
        return $this->verifyFile($path, $ext);
    }

    public function photoUri($collect)
    {
        if ($collect) {
            collect($collect)->map(function ($item) {
                if (isset($item->photo)) {
                    $item->photo = asset($item->photo);
                }
            });
        }
        return $collect;
    }

    public function base64EncodeImage($filePath)
    {
        $imgInfo = getimagesize($filePath);
        $imgData = file_get_contents($filePath);
        if ($imgInfo['mime'] && $imgData) {
            return 'data:' . $imgInfo['mime'] . ';base64,' . base64_encode($imgData);
        } else {
            return null;
        }
    }
}