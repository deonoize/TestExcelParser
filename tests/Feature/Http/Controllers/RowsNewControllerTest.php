<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;

class RowsNewControllerTest extends TestCase
{
    public function testIndex()
    {
        $this->get(route('rows.new'))
             ->assertSuccessful();
    }
}
