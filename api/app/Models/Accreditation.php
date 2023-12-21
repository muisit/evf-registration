<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Services\PDFService;
use Carbon\Carbon;

class Accreditation extends Model
{
    protected $table = 'TD_Accreditation';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    public static function makeDirty(Fencer $fencer, Event $event)
    {
        $cnt = Accreditation::where("fencer_id", $fencer->getKey())->where("event_id", $event->getKey())->count();

        if ($cnt == 0) {
            // we create an empty accreditation to signal the queue that this set needs to be reevaluated
            $dt = new Accreditation();
            $dt->fencer_id = $fencer->getKey();
            $dt->event_id = $event->getKey();
            $dt->data = json_encode([]);

            $tmpl = AccreditationTemplate::where('event_id', $event->getKey())->first();
            if (!empty($tmpl)) {
                $dt->template_id = $tmpl->getKey();
                $dt->file_id = null;
                $dt->generated = null;
                $dt->is_dirty = strftime('%F %T');
                $dt->save();
            }
            // else if there are no templates, there are no accreditations (yet)
        }
        else {
            Accreditation::where('fencer_id', $fencer->getKey())->where('event_id', $event->getKey())->update([
                'is_dirty' => Carbon::now()->toDateTimeString()
            ]);
        }
    }

    public function fencer(): BelongsTo
    {
        return $this->belongsTo(Fencer::class, 'fencer_id', 'fencer_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AccreditationTemplate::class, 'template_id', 'id');
    }

    public function path()
    {
        return PDFService::pdfPath($this->event, sprintf("badge_%d.pdf", $this->id));
    }

    public function delete()
    {
        $path = $this->path();
        if (file_exists($path)) {
            @unlink($path);
        }
        return parent::delete();
    }

    private function createControlDigit(string $id)
    {
        // create a control number by adding up all the digits
        $total = 0;
        for ($i = 0; $i < strlen($id); $i++) {
            $total += intval($id[$i]);
        }
        $control = (10 - ($total % 10) % 10);
        return $control;
    }

    public function createId($tries = 0)
    {
        $id1 = random_int(101, 999);
        $id2 = random_int(101, 999);

        $id = sprintf("%d%03d%03d", $this->event_id, $id1, $id2);

        // see if there is an open accreditation with this ID. In that case, we generate a new
        $a = Accreditation::where('fe_id', $this->fe_id)->first();
        if (!empty($a) && $a->exists) {
            if ($tries < 10) {
                return $this->createId($tries + 1);
            }
            else {
                // this should not happen, but we are catching the theoretical case
                // start with a 0, which no regular id should ever do
                $id = '0' . $this->event_id . '' . $this->getKey();
            }
        }
        $this->fe_id = $id . $this->createControlDigit($id);

        return $this->fe_id;
    }
}
