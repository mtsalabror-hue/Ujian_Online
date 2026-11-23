<?php

namespace Tests\Feature;

use Tests\TestCase;

class AplikasiBootTest extends TestCase
{
    public function test_aplikasi_bisa_boot(): void
    {
        $this->assertTrue(app()->bound('config'));
        $this->assertSame('testing', config('app.env'));
    }
}
