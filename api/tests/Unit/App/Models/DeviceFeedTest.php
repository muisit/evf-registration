<?php

namespace Tests\Unit\App\Models;

use App\Models\DeviceFeed;
use App\Models\DeviceUser;
use App\Models\WPOption;
use App\Models\WPPost;
use Tests\Support\Data\DeviceFeed as Data;
use Tests\Support\Data\DeviceUser as DeviceUserData;
use Tests\Support\Data\WPOption as OptData;
use Tests\Support\Data\WPPost as PostData;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tests\Unit\TestCase;

class DeviceFeedTest extends TestCase
{
    public function testRelations()
    {
        $data = DeviceFeed::find(Data::FEED1);
        $this->assertNotEmpty($data);
        $this->assertNotEquals('this-is-a-uuid', $data->uuid); // overwritten at create
        $this->assertEquals($data->type, DeviceFeed::NOTIFICATION);
        $this->assertEquals('you have been notified', $data->title);
        $this->assertEquals('This is a generic notification', $data->content);
        $this->assertEmpty($data->content_id);
        $this->assertEmpty($data->content_model);
        $this->assertEquals('en', $data->locale);
        $this->assertNotEmpty($data->created_at);
        $this->assertNotEmpty($data->updated_at);

        $this->assertInstanceOf(BelongsToMany::class, $data->users());
        $this->assertInstanceOf(DeviceUser::class, $data->users[0]);
        $this->assertEquals(DeviceUserData::DEVICEUSER1, $data->users[0]->getKey());
    }

    public function testSave()
    {
        $data = new DeviceFeed();
        $data->title = '';
        $data->content = '';
        $data->locale = 'nl';
        $data->type = DeviceFeed::NEWS;
        $this->assertEmpty($data->uuid);
        $this->assertEmpty($data->created_at);
        $this->assertEmpty($data->updated_at);

        $data->save();
        $this->assertNotEmpty($data->uuid);
        $this->assertNotEmpty($data->created_at);
        $this->assertNotEmpty($data->updated_at);

        sleep(1);
        $data->title = 'A Title';
        $data->save();
        $this->assertNotEmpty($data->created_at);
        $this->assertNotEmpty($data->updated_at);
        $this->assertNotEquals($data->created_at, $data->updated_at);
    }

    public function testFromWpContent()
    {
        $data = DeviceFeed::find(Data::FEED1);
        $tags = ['br', 'a', 'b', 'i', 'em', 'u', 'ul', 'ol', 'li',
          'table', 'tr', 'td', 'th', 'thead', 'tbody', 'tfooter',
          'div', 'p', 'span', 'strong',
          'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
          'style', 'header', 'footer', 'nav'];
        foreach ($tags as $tag) {
            $txt = "<$tag>DummyData</$tag>";
            $result = $data->fromWPContent($txt);
            $this->assertEquals($txt, $result);
        }

        $otherTags = ['image', 'button', 'form', 'input', 'select', 'option',
            'link', 'html', 'body', 'script', 'field', 'abbr', 'accronym', 'address',
            'applet', 'area', 'article', 'aside', 'audio', 'blockquote', 'canvas',
            'center', 'code', 'colgroup', 'col', 'data', 'datalist', 'embed',
            'fieldset', 'iframe', 'frame', 'frameset', 'head',
            'label', 'legend', 'noembed', 'noscript', 'object', 'optgroup',
            'pre', 'q', 'section', 'source', 'svg', 'title', 'video'
        ];
        foreach ($otherTags as $tag) {
            $txt = "<$tag>DummyData</$tag>";
            $result = $data->fromWPContent($txt);
            $this->assertEquals('DummyData', $result);
        }
    }

    public function testPermalink()
    {
        $feed = new DeviceFeed();
        $post = WPPost::find(PostData::BLOG1);
        $opt = WPOption::find(OptData::OPT1);
        $opt->option_value = 'dummy/%year%/dommy';
        $opt->save();
        $this->assertEquals("http://localhost/dummy/2020/dommy", $feed->createPermalink($post));

        $opt->option_value = 'dummy/%monthnum%/dommy';
        $opt->save();
        $this->assertEquals("http://localhost/dummy/01/dommy", $feed->createPermalink($post));

        $opt->option_value = 'dummy/%day%/dommy';
        $opt->save();
        $this->assertEquals("http://localhost/dummy/01/dommy", $feed->createPermalink($post));

        $opt->option_value = 'dummy/%hour%/dommy';
        $opt->save();
        $this->assertEquals("http://localhost/dummy/12/dommy", $feed->createPermalink($post));

        $opt->option_value = 'dummy/%minute%/dommy';
        $opt->save();
        $this->assertEquals("http://localhost/dummy/34/dommy", $feed->createPermalink($post));

        $opt->option_value = 'dummy/%second%/dommy';
        $opt->save();
        $this->assertEquals("http://localhost/dummy/56/dommy", $feed->createPermalink($post));

        $opt->option_value = 'dummy/%postname%/dommy';
        $opt->save();
        $this->assertEquals("http://localhost/dummy/blog1/dommy", $feed->createPermalink($post));

        $opt->option_value = 'dummy/%post_id%/dommy';
        $opt->save();
        $this->assertEquals("http://localhost/dummy/" . PostData::BLOG1 . "/dommy", $feed->createPermalink($post));

        $opt->option_value = 'dummy/%pagename%/dommy';
        $opt->save();
        $this->assertEquals("http://localhost/dummy/blog1/dommy", $feed->createPermalink($post));
    }
}
