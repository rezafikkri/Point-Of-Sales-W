<?php

namespace App\Filters;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FilterTestTrait;

class HasSignedInTest extends CIUnitTestCase
{
    use FilterTestTrait;

    public function testHasSignedInFilterAppliedToSignInRoute():void {
        $this->assertFilter('sign_in', 'before', 'hasSignedIn');
    }

    public function testHasSignedInAccessRedirects():void {
        // set session for has signed in simulation
        $_SESSION['posw_sign_in_status'] = true;
        $_SESSION['posw_user_level'] = 'admin';

        $caller = $this->getFilterCaller('hasSignedIn', 'before');
        $result = $caller();

        $this->assertInstanceOf('CodeIgniter\HTTP\RedirectResponse', $result);
    }
}
