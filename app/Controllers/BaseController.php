<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\{CLIRequest, IncomingRequest, RequestInterface, ResponseInterface};
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * Open delimiter message for add delimiter message
     * 
     * @var string
     */
    protected $openDelimiterMessage = '<small class="form-message form-message--danger">';

    /**
     * Close delimiter message for add delimiter message
     * 
     * @var string
     */
    protected $closeDelimiterMessage = '</small>';

    /**
     * List field for ignore from add delimiter message
     * 
     * @var array
     */
    protected $ignoreMessages;

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        $this->session = session();
    }

    protected function addDelimiterMessages(array $messages): array {
        $newMessages = [];

        foreach ($messages as $key => $value) {
            // if ignore messages not null and key exists in ignore array
            if ($this->ignoreMessages && in_array($key, $this->ignoreMessages)) {
                $newMessages[$key] = $value;
            } else {
                $newMessages[$key] = $this->openDelimiterMessage.$value.$this->closeDelimiterMessage;
            }
        }

        return $newMessages;
    }

    protected function validateUserSignInPassword($userSignInPassword): bool
    {
        if (empty(trim($userSignInPassword))) {
            $this->userSignInPasswordErrorMessage = 'Bidang Password Mu diperlukan.';
            return false;
        }
        
        $passwordHash = $this->usersModel->getOne($_SESSION['sign_in_user_id'], 'password')['password'];
        if (!password_verify($userSignInPassword, $passwordHash)) {
            $this->userSignInPasswordErrorMessage = 'Password salah.';
            return false;
        }       
        return true;
    }
}
