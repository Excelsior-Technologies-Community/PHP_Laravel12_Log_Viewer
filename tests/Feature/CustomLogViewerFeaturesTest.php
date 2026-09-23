<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CustomLogViewerFeaturesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $path = storage_path('logs/laravel.log');
        if (!File::exists($path)) {
            File::put($path, '');
        }
    }

    /**
     * Test test-log generator endpoint for standard log level.
     */
    public function test_generate_test_log_entry(): void
    {
        $response = $this->post(route('admin.logs.generate'), [
            'type' => 'error',
            'message' => 'Test error message from automated test suite.',
        ]);

        $response->assertRedirect(route('admin.logs'));
        $response->assertSessionHas('success');

        $logContent = File::get(storage_path('logs/laravel.log'));
        $this->assertStringContainsString('Test error message from automated test suite.', $logContent);
    }

    /**
     * Test test-log generator for mock exceptions.
     */
    public function test_generate_mock_exception_entry(): void
    {
        $response = $this->post(route('admin.logs.generate'), [
            'type' => 'exception',
            'exception_type' => 'database',
        ]);

        $response->assertRedirect(route('admin.logs'));
        $response->assertSessionHas('success');

        $logContent = File::get(storage_path('logs/laravel.log'));
        $this->assertStringContainsString('QueryException: SQLSTATE[HY000]', $logContent);
    }

    /**
     * Test telemetry analytics & 24-hour health heatmap computation.
     */
    public function test_telemetry_analytics_and_heatmap_rendering(): void
    {
        $response = $this->get(route('admin.logs'));

        $response->assertStatus(200);
        $response->assertSee('Log Telemetry Analytics');
        $response->assertSee('24-Hour Health Heatmap');
        $response->assertSee('Health Status:');
    }

    /**
     * Test live monitoring JSON API endpoint.
     */
    public function test_live_monitoring_json_endpoint(): void
    {
        $response = $this->get(route('admin.logs.live'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'logs',
            'statistics' => [
                'total',
                'info',
                'warning',
                'error',
                'debug',
                'critical',
                'alert',
                'notice',
                'emergency',
            ],
            'updated_at',
            'total',
        ]);
    }
}
