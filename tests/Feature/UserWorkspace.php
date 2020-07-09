<?php

namespace Tests\Feature;

use App\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserWorkspace extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        if(Service::query()->exists()) {
            $response = $this->get('/api/user/service/freeTime/'.Service::query()->value('id'));
            $response->assertStatus(200);
        }
    }
}
