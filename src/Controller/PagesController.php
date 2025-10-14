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
        if (isset($this->Authentication)) {
            $this->Authentication->allowUnauthenticated(['display']);
        }
    }

    public function display(string ...$path)
    {
        $page = $path[0] ?? 'home';

        if ($page === 'home') {
            $SiteSettings = $this->fetchTable('SiteSettings');
            $home = [];
            foreach ($SiteSettings->find()->where(['setting_key LIKE' => 'home_%']) as $row) {
                $home[$row->setting_key] = (string)$row->setting_value;
            }
            $this->set('home', $home);

            // Backwards-compat with previously set vars
            $this->set('homeHeroTitle',   $home['home_hero_title']   ?? 'Small-Batch Cheeses, Crafted with Love');
            $this->set('homeHeroLead',    $home['home_hero_lead']    ?? 'From our family farm to your table. Experience the finest handmade cheeses, delivered fresh to your door with temperature-controlled care.');
            $this->set('homeCtaTitle',    $home['home_cta_title']    ?? 'Ready to Experience Premium Cheese?');
            $this->set('homeCtaSubtitle', $home['home_cta_subtitle'] ?? 'Start your artisan cheese journey today. Browse our full collection and taste the difference that quality makes.');
        }

        return $this->render($page);
    }
}
