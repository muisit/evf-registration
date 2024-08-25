<?php

namespace App\Models;

use App\Models\Schemas\BlockStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class DeviceFeed extends Model
{
    public const NOTIFICATION = 1;
    public const NEWS = 2;
    public const MESSAGE = 3;
    public const RESULT = 4;
    public const RANKING = 5;

    protected $table = 'device_feeds';
    protected $guarded = [];

    public static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(DeviceFeed::class, 'device_user_feeds', 'device_feed_id', 'device_user_id');
    }

    public function fromWPContent($txt)
    {
        // allow only some basic structuring and styling tags
        // explicitely no: image, script, canvas, head, html, embed
        return strip_tags(
            $txt,
            [
                'br', 'a', 'b', 'i', 'em', 'u', 'ul', 'ol', 'li',
                'table', 'tr', 'td', 'th', 'thead', 'tbody', 'tfooter',
                'div', 'p', 'span',
                'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                'style', 'header', 'footer', 'nav',
            ]
        );
    }

    public function createPermalink($post)
    {
        $permalink = WPOption::where('option_name', 'permalink_structure')->first();
        $site = WPOption::where('option_name', 'home')->first();

        $rewritecode = array(
            '%year%',
            '%monthnum%',
            '%day%',
            '%hour%',
            '%minute%',
            '%second%',
            '%postname%',
            '%post_id%',
//            '%category%',
//            '%author%',
            '%pagename%',
        );
        $date = explode(' ', str_replace(array( '-', ':' ), ' ', $post->post_date));
        $rewritereplace = array(
            $date[0],
            $date[1],
            $date[2],
            $date[3],
            $date[4],
            $date[5],
            $post->post_name,
            $post->ID,
//            $category,
//            $author,
            $post->post_name,
        );
        return $site->option_value . '/' . str_replace($rewritecode, $rewritereplace, $permalink->option_value);
    }
}
