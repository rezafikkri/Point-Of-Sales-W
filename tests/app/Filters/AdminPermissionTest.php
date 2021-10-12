<?php

namespace App\Filters;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FilterTestTrait;

class AdminPermissionTest extends CIUnitTestCase
{
    use FilterTestTrait;

    public function testAdminPermissionFilterAppliedToAdminRoute():void {
        $this->assertFilter('admin', 'before', 'adminPermission');
    }

    public function testUnsignedInAccessRedirects():void {
        $caller = $this->getFilterCaller('adminPermission', 'before');
        $result = $caller();

        $this->assertInstanceOf('CodeIgniter\HTTP\RedirectResponse', $result);
    }
}
