<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Services\PDFService;
use Carbon\Carbon;

class Accreditation extends Model
{
    protected $table = 'TD_Accreditation';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    public static function booted()
    {
        static::deleting(function ($model) {
            // deleting an accreditation after the event has started should not happen
            // However, this may occur in the test phase before

            // remove all entries in the AccreditationAudit table
            AccreditationAudit::where('accreditation_id', $model->getKey())->delete();
            // remove all documents linked to this accreditation
            AccreditationDocument::where('accreditation_id', $model->getKey())->delete();
            // Remove all users linked to this accreditation
            // Loop this, because there should only be one or two rows
            foreach (AccreditationUser::where('accreditation_id', $model->getKey())->get() as $user) {
                $user->delete(); // invoke method to get additional delete actions
            };

            // delete the badge
            $path = $model->path();
            if (file_exists($path)) {
                @unlink($path);
            }
        });
    }

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

    public function documents(): HasMany
    {
        return $this->hasMany(AccreditationDocument::class);
    }

    public function path($makeAbsolute = true)
    {
        return PDFService::pdfPath($this->event, sprintf("badges/badge_%d.pdf", $this->id), $makeAbsolute);
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
        return sprintf('11%s%1d%04d', $this->fe_id, self::createControlDigit($this->fe_id ?? ''), 0);
    }

    public function getDatesOfAccreditation()
    {
        $dates = [];
        $registrations = Registration::where('registration_fencer', $this->fencer->getKey())
            ->where('registration_mainevent', $this->event->getKey())
            ->with('sideEvent')
            ->with('sideEvent.competition')
            ->get();
        foreach ($registrations as $registration) {
            if (!empty($registration->sideEvent) && !empty($registration->sideEvent->competition)) {
                $dt = (new Carbon($registration->sideEvent->starts))->format('Y-m-d');
                $dates[$dt] = true;
            }
        }
        $lst = array_keys($dates);
        sort($lst);
        return array_map(
            function ($dt) {
                return (new Carbon($dt))->format('D d');
            },
            $lst
        );
    }
}
