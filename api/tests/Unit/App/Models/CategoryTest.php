<?php

namespace Tests\Unit\App\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\Unit\TestCase;

class CategoryTest extends TestCase
{
    public function testRelations()
    {
        $categories = Category::all();
        $this->assertCount(7, $categories);
    }
}
