<?php

namespace App\Filters;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FilterTestTrait;

class CashierPermissionTest extends CIUnitTestCase
{
    use FilterTestTrait;

    public function testCashierPermissionFilterAppliedToCashierRoute(): void
    {
        $this->assertFilter('kasir', 'before', 'cashierPermission');
    }

    public function testUnsignedInAccessRedirects(): void
    {
        $caller = $this->getFilterCaller('cashierPermission', 'before');
        $result = $caller();

        $this->assertInstanceOf('CodeIgniter\HTTP\RedirectResponse', $result);
    }
}
