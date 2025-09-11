<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;

class PagesController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();

    }

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);


    }


    public function display(string ...$path)
    {
        $page = $path[0] ?? 'home';
        return $this->render($page);
    }
}
