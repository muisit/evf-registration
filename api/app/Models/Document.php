<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class Document extends Model
{
    protected $table = 'TD_Document';
    //protected $primaryKey = 'id';
    public $timestamps = false;

    protected $casts = [
        "config" => "array",
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

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

    public function setConfig($vals)
    {
        $this->config = array_merge($this->config ?? [], $vals);
    }
}
