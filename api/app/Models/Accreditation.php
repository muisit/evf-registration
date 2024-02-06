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

    public static function makeDirty(Fencer $fencer, ?Event $event)
    {
        $sql = Accreditation::where("fencer_id", $fencer->getKey());
        if (!empty($event)) {
            $sql = $sql->where("event_id", $event->getKey());
        }
        $cnt = $sql->count();

        if ($cnt == 0 && !empty($event)) {
            // we create an empty accreditation to signal the queue that this set needs to be reevaluated
            $dt = new Accreditation();
            $dt->fencer_id = $fencer->getKey();
            $dt->event_id = $event->getKey();
            $dt->data = json_encode([]);

            $tmpl = AccreditationTemplate::where('event_id', $event->getKey())->where('is_default', 'N')->first();
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
            $sql = Accreditation::where('fencer_id', $fencer->getKey());
            if (!empty($event)) {
                $sql = $sql->where("event_id", $event->getKey());
            }
            $sql->update([
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

    public function path($makeAbsolute = true)
    {
        return PDFService::pdfPath($this->event, sprintf("badges/badge_%d.pdf", $this->id), $makeAbsolute);
    }

    public function delete()
    {
        // delete all linked AccreditationUsers
        AccreditationUser::where('accreditation_id', $this->getKey())->delete();
        $path = $this->path();
        if (file_exists($path)) {
            @unlink($path);
        }
        return parent::delete();
    }

    public static function createControlDigit(string $id)
    {
        // create a control number by adding up all the digits
        $total = 0;
        for ($i = 0; $i < strlen($id); $i++) {
            $total += intval($id[$i]);
        }
        $control = (10 - ($total % 10)) % 10;
        return $control;
    }

    public function createId($tries = 100)
    {
        while (true) {
            if ($tries > 1) {
                $tries--;
                $id1 = random_int(101, 999);
                $id2 = random_int(101, 999);
                $id = sprintf("1%03d%03d", $id1, $id2);
                $a = Accreditation::where('fe_id', $id)->first();
                if (empty($a)) {
                    break;
                }
            }
            else {
                // this should not happen, but we are catching the theoretical case
                // start with a 0, which no regular id should ever do
                $id = sprintf("0%06d", $this->getKey());
                break;
            }
        }
        $this->fe_id = $id;

        return $this->fe_id;
    }

    public function getFullAccreditationId()
    {
        return sprintf('11%s%1d%04d', $this->fe_id, self::createControlDigit($this->fe_id), 0);
    }
}
