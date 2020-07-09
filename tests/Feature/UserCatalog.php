<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserCatalog extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $response = $this->get('/api/user/catalog/list');
        $response->assertStatus(200);

        $response = $this->get('/api/user/catalog/list/1/10');
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        if(count($response->json()['data']) > 0) {

            $response = $this->get('/api/user/catalog/workshops/'.$response->json()['data'][0]['id']);
            $response->assertStatus(200);

            $response = $this->get('/api/user/catalog/workshops/'.$response->json()['data'][0]['id'].'/1/10');
            $response->assertStatus(200);
        }
    }
}
