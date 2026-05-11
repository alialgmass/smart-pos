<?php

namespace Modules\Shared\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ModuleRoutesLoadTest extends TestCase
{
    #[Test]
    public function all_generated_module_route_files_exist(): void
    {
        $root = dirname(__DIR__, 4);
        $modules = [
            'Shared',
            'Tenancy',
            'Identity',
            'Inventory',
            'Sales',
            'Customers',
            'Restaurant',
            'Reports',
            'Settings',
            'Offline',
            'Billing',
        ];

        foreach ($modules as $module) {
            $this->assertFileExists($root."/Modules/{$module}/routes/web.php");
            $this->assertFileExists($root."/Modules/{$module}/routes/api.php");
        }
    }
}
