<?php

namespace App\Libs\Traits;

use Illuminate\Http\Request;
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

    public function requestIsEmpty(Request $request, array $needParam = [], $operator = null)
    {
        $bool = false;
        $all = $request->all();
        if (sizeof($all)) {
            foreach ($all as $key => $val) {
                if (strtolower($operator) == 'or' && in_array($key, $needParam) && !empty($key)) {
                    break;
                }
                if (in_array($key, $needParam) && empty($val) && (empty($operator) || strtolower($operator) == 'and')) {
                    $bool = true;
                    break;
                }
            }
        } else {
            $bool = true;
        }
        return $bool;
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
}