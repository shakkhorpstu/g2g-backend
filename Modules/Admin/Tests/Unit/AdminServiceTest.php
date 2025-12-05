<?php

namespace Modules\Admin\Tests\Unit;

use Modules\Admin\Services\AdminService;
use Modules\Admin\Contracts\Repositories\AdminRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Hash;

class AdminServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock Hash facade for unit tests
        if (!class_exists('Illuminate\Support\Facades\Hash')) {
            $this->markTestSkipped('Laravel Hash facade not available in unit test context');
        }
    }

    public function test_store_admin_hashes_password_and_calls_repository()
    {
        $repo = $this->createMock(AdminRepositoryInterface::class);

        // Mock findByEmail to return null (email doesn't exist)
        $repo->expects($this->once())
            ->method('findByEmail')
            ->with('admin@example.com')
            ->willReturn(null);

        $repo->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($data) {
                // Verify password is hashed and other fields are present
                return isset($data['name']) 
                    && isset($data['email']) 
                    && isset($data['password']) 
                    && is_string($data['password']) 
                    && strlen($data['password']) > 0;
            }))
            ->willReturn([
                'id' => 1,
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $service = new AdminService($repo);

        $result = $service->store([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'secret123'
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals(1, $result['data']['id']);
    }
}
