<?php

namespace App\Models;

class Document extends Model
{
    protected $table = 'TD_Document';
    //protected $primaryKey = 'id';
    public $timestamps = false;

    public function deleteByName()
    {
        // make sure we delete all summary documents for a set if one of them becomes dirty
        $docs = $this->findByName($this->name);
        foreach ($docs as $doc) {
            $doc->delete();
        }
    }

    public static function findByName($name)
    {
        return static::where('name', 'like', "$name%");
    }

    public function delete()
    {
        if ($this->fileExists()) {
            @unlink($this->getPath());
        }
        return parent::delete();
    }

    public function fileExists()
    {
        return file_exists($this->getPath());
    }

    public function getPath()
    {
        return storage_path('app/documents/' . $this->path);
    }
}
