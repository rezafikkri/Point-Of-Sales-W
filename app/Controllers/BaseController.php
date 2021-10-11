<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
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
    protected $openDelimiter = '<small class="form-message form-message--danger">';
    protected $closeDelimiter = '</small>';
    protected $ignore;

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
            // if ignore not null and key exists in ignore array
            if ($this->ignore && in_array($key, $this->ignore)) {
                $newMessages[$key] = $value;
            } else {
                $newMessages[$key] = $this->openDelimiter.$value.$this->closeDelimiter;
            }
        }

        return $newMessages;
    }

    protected function createIndoErrorMessages(array $rules): array
    {
        $messages = [];

        foreach ($rules as $rule) {
            switch ($rule) {
                case 'required':
                    $messages = array_merge($messages, [$rule => '{field} tidak boleh kosong!']);
                    break;
                case 'in_list':
                    $messages = array_merge($messages, [$rule => '{field} harus salah satu dari: {param}!']);
                    break;
                case 'min_length':
                    $messages = array_merge($messages, [$rule => '{field} paling sedikit {param} karakter!']);
                    break;
                case 'max_length':
                    $messages = array_merge($messages, [$rule => '{field} tidak boleh melebihi {param} karakter!']);
                    break;
                case 'is_unique':
                    $messages = array_merge($messages, [$rule => '{field} sudah ada!']);
                    break;
                case 'integer':
                    $messages = array_merge($messages, [$rule => '{field} harus berupa angka dan tanpa desimal!']);
                    break;
            }
        }
        return $messages;
    }
}
