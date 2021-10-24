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

    // property for add delimiter messages
    protected $openDelimiterMessages = '<small class="form-message form-message--danger">';
    protected $closeDelimiterMessages = '</small>';
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
                $newMessages[$key] = $this->openDelimiterMessages.$value.$this->closeDelimiterMessages;
            }
        }

        return $newMessages;
    }
}
