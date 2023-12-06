<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function template(): BelongsTo
    {
        return $this->belongsTo(AccreditationTemplate::class, 'template_id', 'id');
    }

    public function path()
    {
        return sprintf("accreditations/event_%d/badge_%d.pdf", $this->event_id, $this->id);
    }
}
