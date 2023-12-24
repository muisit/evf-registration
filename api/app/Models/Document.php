<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use App\Support\Services\PDFService;

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

    public function getPath($makeAbsolute = true)
    {
        return PDFService::pdfPath($this->event, 'documents/' . $this->path, $makeAbsolute);
    }

    public function setConfig($vals)
    {
        $this->config = array_merge($this->config ?? [], $vals);
    }

    public function validate()
    {
        return !empty($this->hash) && $this->hash == $this->createHash();
    }

    private function accreditations()
    {
        $accreditationIds = $this->config['accreditations'] ?? [];
        return Accreditation::whereIn('id', $accreditationIds)->with(['fencer', 'event', 'template'])->get();
    }

    public function sortFiles()
    {
        $files = [];
        $accreditations = $this->accreditations();

        // check that all files exist
        foreach ($accreditations as $a) {
            if (!empty($a->is_dirty)) {
                \Log::error("Dirty accreditation prevents sorting files " . $this->getKey() . '/' . $a->getKey());
                return null;
            }

            $path = $a->path();
            if (!file_exists($path)) {
                \Log::error("missing PDF $path prevents sorting files " . $this->getKey() . '/' . $a->getKey() . '/' . $path);
                $a->is_dirty = date('Y-m-d H:i:s');
                $a->save();
                return null;
            }
        }

        foreach ($accreditations as $a) {
            $hash = $a->file_hash;
            $key = $a->fencer->getFullName() . "~" . $a->getKey();
            $files[$key] = ["file" => $a->path(), "hash" => $hash, "accreditation" => $a];
        }

        // sort the files by fencer name
        // Sorting makes it easier for the end user to find missing accreditations
        // Also, sorting is vital to make sure the overall hash is created in the
        // same way
        ksort($files, SORT_NATURAL);
        return $files;
    }

    public function createHash()
    {
        $files = $this->sortFiles();
        if (empty($files)) {
            return '';
        }

        // accumulate all hashes to get at an overall hash
        $acchash = "";
        foreach ($files as $k => $v) {
            $acchash .= $v["hash"];
        }
        return hash('sha256', $acchash);
    }

    public function deleteSiblings()
    {
        foreach ($this->event->documents()->where('type', $this->type)->where('type_id', $this->type_id)->get() as $sibling) {
            // delete document and database record
            $sibling->delete();
        };
    }
}
