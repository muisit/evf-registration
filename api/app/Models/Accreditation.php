<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Accreditation extends Model
{
    protected $table = 'TD_Accreditation';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public static function makeDirty(Fencer $fencer, Event $event)
    {
        $cnt = Accreditation::where("fencer_id", $fencer->getKey())->where("event_id", $event->getKey())->count();

        if ($cnt == 0) {
            // we create an empty accreditation to signal the queue that this set needs to be reevaluated
            $dt = new Accreditation();
            $dt->fencer_id = $fid;
            $dt->event_id = $eid;
            $dt->data = json_encode(array());

            $tmpl = AccreditationTemplate::first();
            if (!empty($tmpl)) {
                $dt->template_id = $tmpl->getKey();
            }
            $dt->file_id = null;
            $dt->generated = null;
            $dt->is_dirty = strftime('%F %T');
            $dt->save();
        }
        else {
            Accreditation::where('fencer_id', $fencer->getKey())->where('event_id', $event->getKey())->update([
                'is_dirty' => Carbon::now()->toDateTimeString()
            ]);
        }
    }
}
