<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         3.3.4
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Event\EventInterface;

/**
 * Error Handling Controller
 *
 * Controller used by ExceptionRenderer to render error responses.
 */
class ErrorController extends AppController
{
    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize(): void
    {
        // Only add parent::initialize() if you are confident your appcontroller is safe.
    }

    /**
     * beforeFilter callback.
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event Event.
     * @return \Cake\Http\Response|null|void
     */
    public function beforeFilter(EventInterface $event)
    {
    }

    /**
     * beforeRender callback.
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event Event.
     * @return \Cake\Http\Response|null|void
     */
    public function beforeRender(EventInterface $event)
    {
        parent::beforeRender($event);

        $this->viewBuilder()->setTemplatePath('Error');
        
        // Set appropriate template based on error code
        $error = $event->getSubject();
        $code = 500; // Default to 500 error
        
        if ($error !== null) {
            // For missing controller/page errors, always use 404 template
            $errorClass = get_class($error);
            if (strpos($errorClass, 'MissingController') !== false || 
                strpos($errorClass, 'NotFoundException') !== false ||
                strpos($errorClass, 'MissingRoute') !== false ||
                strpos($errorClass, 'MissingAction') !== false) {
                $this->viewBuilder()->setTemplate('error404');
                return;
            }
            
            // Try to get error code from different possible sources
            if (method_exists($error, 'getCode')) {
                $code = $error->getCode();
            } elseif (method_exists($error, 'getHttpCode')) {
                $code = $error->getHttpCode();
            }
        }
        
        // Set template based on error code
        switch ($code) {
            case 404:
                $this->viewBuilder()->setTemplate('error404');
                break;
            case 400:
                $this->viewBuilder()->setTemplate('error400');
                break;
            case 500:
            default:
                $this->viewBuilder()->setTemplate('error500');
                break;
        }
    }

    /**
     * afterFilter callback.
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event Event.
     * @return \Cake\Http\Response|null|void
     */
    public function afterFilter(EventInterface $event)
    {
    }
}
