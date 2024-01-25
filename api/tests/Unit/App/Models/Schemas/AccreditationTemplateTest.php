<?php

namespace Tests\Unit\App\Models\Schemas;

use App\Models\Schemas\AccreditationTemplate as Schema;
use App\Models\AccreditationTemplate;
use Tests\Support\Data\AccreditationTemplate as Data;
use Tests\Unit\TestCase;

class AccreditationTemplateTest extends TestCase
{
    public function testEmpty()
    {
        $schema = new Schema(null);
        $this->assertEmpty($schema->eventId);
        $this->assertEquals('N', $schema->isDefault);
        $this->assertEmpty($schema->content);
        $this->assertEmpty($schema->name);
        $this->assertEmpty($schema->id);
    }

    public function testCreate()
    {
        $template = AccreditationTemplate::where('id', Data::COUNTRY)->first();
        $schema = new Schema($template);

        $this->assertEquals($template->name, $schema->name);
        $this->assertEquals($template->content, json_encode($schema->content));
        $this->assertEquals($template->is_default, $schema->isDefault);
        $this->assertEquals($template->event_id, $schema->eventId);
        $this->assertEquals($template->id, $schema->id);
    }

    public function testConversions()
    {
        $template = AccreditationTemplate::where('id', Data::COUNTRY)->first();
        $template->is_default = 'R';
        $schema = new Schema($template);
        $this->assertEquals('N', $schema->isDefault);

        $template->is_default = 1;
        $schema = new Schema($template);
        $this->assertEquals('N', $schema->isDefault);

        $template->is_default = null;
        $schema = new Schema($template);
        $this->assertEquals('N', $schema->isDefault);

        $template->content = 1; // although valid json, not an object
        $schema = new Schema($template);
        $this->assertEmpty($schema->content);

        $template->content = '{"a": 1}';
        $schema = new Schema($template);
        $this->assertTrue(is_object($schema->content));

        $template->content = 'aaaaa';
        $schema = new Schema($template);
        $this->assertEmpty($schema->content);
    }
}
