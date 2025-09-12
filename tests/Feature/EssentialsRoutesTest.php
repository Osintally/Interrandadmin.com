<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EssentialsRoutesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the essentials dashboard route.
     *
     * @return void
     */
    public function testEssentialsDashboardRoute()
    {
        $response = $this->get('/essentials/dashboard');

        $response->assertStatus(200);
    }

    /**
     * Test the memos resource routes.
     *
     * @return void
     */
    public function testMemosRoutes()
    {
        $response = $this->get('/essentials/memos');
        $response->assertStatus(200);

        $response = $this->post('/essentials/memos', [
            'title' => 'Test Memo',
            'content' => 'This is a test memo.',
        ]);
        $response->assertStatus(201);

        $response = $this->get('/essentials/memos/1');
        $response->assertStatus(200);

        $response = $this->put('/essentials/memos/1', [
            'title' => 'Updated Memo',
        ]);
        $response->assertStatus(200);

        $response = $this->delete('/essentials/memos/1');
        $response->assertStatus(200);
    }

    /**
     * Test the document download route.
     *
     * @return void
     */
    public function testDocumentDownloadRoute()
    {
        $response = $this->get('/essentials/document/1/download');

        $response->assertStatus(200);
    }

    /**
     * Test the HRM dashboard route.
     *
     * @return void
     */
    public function testHrmDashboardRoute()
    {
        $response = $this->get('/hrm/dashboard');

        $response->assertStatus(200);
    }
}