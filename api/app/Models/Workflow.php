<?php

namespace App\Models;

class Workflow extends Model
{
    public $timestamps = false;
    protected $casts = [
        "sandbox" => "array",
    ];

    public static function booted()
    {
        static::deleted(function ($model) {
            $files = $model->sandbox['files'] ?? [];
            foreach ($files as $file) {
                if (file_exists($file['path'])) {
                    @unlink($file['path']);
                }
            }
        });
    }

    public function addFile($path, $data)
    {
        $files = $this->sandbox['files'] ?? [];
        $fileData = collect($files)->filter(function ($f) use ($path) {
            return (($f['path'] ?? '') == $path);
        })->values();

        if (count($fileData) == 0 || empty($fileData[0])) {
            $fileData = $data;
            $fileData['path'] = $path;
            $files[] = $fileData;
        }
        else {
            $resultData = array_merge($fileData[0], $data); // data overrides fileData
            $files = collect($files)->map(fn ($i) => $i['path'] == $path ? $resultData : $i)->toArray();
        }
        $sb = $this->sandbox;
        $sb['files'] = $files;
        $this->sandbox = $sb;
    }

    public function removeFile($path)
    {
        $files = $this->sandbox['files'] ?? [];
        $newFiles = [];
        foreach ($files as $file) {
            if (isset($file['path']) && $file['path'] == $path) {
                if (file_exists($path)) {
                    @unlink($path);
                }
            }
            else {
                $newFiles[] = $file;
            }
        }
        $sb = $this->sandbox;
        $sb['files'] = $newFiles;
        $this->sandbox = $sb;
    }
}
