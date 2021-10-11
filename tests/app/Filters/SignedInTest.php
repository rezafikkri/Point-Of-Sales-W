<?php

namespace App\Filters;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FilterTestTrait;

class SignedInTest extends CIUnitTestCase
{
    use FilterTestTrait;

    public function testSignedInFilterAppliedToSignInRoute():void {
        $this->assertFilter('sign_in', 'before', 'hasSignedIn');
    }

    public function testSignedInAccessRedirects():void {
        $_SESSION['posw_sign_in_status'] = true;
        $_SESSION['posw_user_level'] = 'admin';

        $caller = $this->getFilterCaller('hasSignedIn', 'before');
        $result = $caller();

        $this->assertInstanceOf('CodeIgniter\HTTP\RedirectResponse', $result);
    }
}
