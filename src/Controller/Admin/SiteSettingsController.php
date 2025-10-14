<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AppController;

class SiteSettingsController extends AppController
{
    private array $homeFields = [
        // Hero
        'home_hero_title'       => ['label' => 'Hero Title',       'type' => 'text',     'default' => 'Small-Batch Cheeses, Crafted with Love'],
        'home_hero_lead'        => ['label' => 'Hero Lead',        'type' => 'textarea', 'default' => 'From our family farm to your table. Experience the finest handmade cheeses, delivered fresh to your door with temperature-controlled care.'],
        'home_hero_image'       => ['label' => 'Hero Image',       'type' => 'image',    'default' => 'cheese-platter.jpg'],

        // Featured section
        'home_featured_title'    => ['label' => 'Featured: Title',    'type' => 'text',     'default' => 'Our Signature Cheeses'],
        'home_featured_subtitle' => ['label' => 'Featured: Subtitle', 'type' => 'textarea', 'default' => 'Handcrafted with passion, aged to perfection'],
        'home_featured_1_image'  => ['label' => 'Featured #1 Image',  'type' => 'image',    'default' => 'cheddar.jpg'],
        'home_featured_2_image'  => ['label' => 'Featured #2 Image',  'type' => 'image',    'default' => 'cows-meadow.jpg'],
        'home_featured_3_image'  => ['label' => 'Featured #3 Image',  'type' => 'image',    'default' => 'farm.jpg'],

        // Trust & values
        'home_trust_1_title' => ['label' => 'Trust #1 Title', 'type' => 'text',     'default' => 'Premium Quality'],
        'home_trust_1_text'  => ['label' => 'Trust #1 Text',  'type' => 'textarea', 'default' => 'Award-winning cheeses crafted with the finest ingredients'],
        'home_trust_2_title' => ['label' => 'Trust #2 Title', 'type' => 'text',     'default' => 'Chilled Delivery'],
        'home_trust_2_text'  => ['label' => 'Trust #2 Text',  'type' => 'textarea', 'default' => 'Temperature-controlled at 4°C from farm to your door'],
        'home_trust_3_title' => ['label' => 'Trust #3 Title', 'type' => 'text',     'default' => 'Family Legacy'],
        'home_trust_3_text'  => ['label' => 'Trust #3 Text',  'type' => 'textarea', 'default' => 'Three generations of cheese-making excellence'],
        'home_trust_4_title' => ['label' => 'Trust #4 Title', 'type' => 'text',     'default' => 'Fresh Daily'],
        'home_trust_4_text'  => ['label' => 'Trust #4 Text',  'type' => 'textarea', 'default' => 'Small batches to ensure peak freshness'],

        // Delivery
        'home_delivery_title' => ['label' => 'Delivery: Title', 'type' => 'text',     'default' => 'Freshness First: Refrigerated Delivery'],
        'home_delivery_lead'  => ['label' => 'Delivery: Lead',  'type' => 'textarea', 'default' => 'We maintain optimal temperature (~4°C) throughout the journey.'],

        // Why choose us
        'home_why_title' => ['label' => 'Why: Title', 'type' => 'text',     'default' => 'Why Choose Curd & Culture?'],
        'home_why_intro' => ['label' => 'Why: Intro', 'type' => 'textarea', 'default' => 'Over 35 years of artisan cheese craftsmanship.'],
        'home_why_image' => ['label' => '“Why” Section Image', 'type' => 'image', 'default' => 'cows-meadow.jpg'],

        // How it works
        'home_how_title'     => ['label' => 'How: Title',     'type' => 'text',     'default' => 'How It Works'],
        'home_how_subtitle'  => ['label' => 'How: Subtitle',  'type' => 'textarea', 'default' => 'From browsing to enjoying — simple, seamless, and fresh'],

        // Testimonials
        'home_test_title'    => ['label' => 'Testimonials: Title',    'type' => 'text',     'default' => 'What Our Customers Say'],
        'home_test_subtitle' => ['label' => 'Testimonials: Subtitle', 'type' => 'textarea', 'default' => 'Join hundreds of happy cheese lovers'],

        // Final CTA
        'home_cta_title'      => ['label' => 'CTA Title',     'type' => 'text',     'default' => 'Ready to Experience Premium Cheese?'],
        'home_cta_subtitle'   => ['label' => 'CTA Subtitle',  'type' => 'textarea', 'default' => 'Start your artisan cheese journey today. Browse our full collection and taste the difference that quality makes.'],
    ];

    public function index()
    {
        $SiteSettings = $this->fetchTable('SiteSettings');

        foreach ($this->homeFields as $key => $meta) {
            $existing = $SiteSettings->find()->where(['setting_key' => $key])->first();
            if (!$existing) {
                $entity = $SiteSettings->newEntity([
                    'setting_key'   => $key,
                    'setting_value' => $meta['default'],
                    'setting_type'  => ($meta['type'] === 'textarea' ? 'html' : 'text'),
                ]);
                $SiteSettings->save($entity);
            }
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            // Handle image uploads first
            $uploadRoot = WWW_ROOT . 'img' . DS . 'uploads' . DS;
            if (!is_dir($uploadRoot)) { @mkdir($uploadRoot, 0775, true); }
            $files = (array)($this->request->getUploadedFiles()['site_settings_files'] ?? []);
            foreach ($this->homeFields as $k => $meta) {
                if (($meta['type'] ?? '') !== 'image') continue;
                $file = $files[$k] ?? null;
                if ($file && $file->getError() === UPLOAD_ERR_OK) {
                    $ctype = (string)$file->getClientMediaType();
                    if (!preg_match('#^image/(png|jpe?g|webp|gif)$#i', $ctype)) {
                        $this->Flash->error("Invalid image type for {$meta['label']}.");
                        continue;
                    }
                    $ext = pathinfo($file->getClientFilename() ?: '', PATHINFO_EXTENSION) ?: 'png';
                    $name = 'home_' . bin2hex(random_bytes(6)) . '.' . strtolower($ext);
                    $file->moveTo($uploadRoot . $name);
                    $row = $SiteSettings->find()->where(['setting_key' => $k])->first();
                    if ($row) {
                        $row->setting_value = 'uploads/' . $name;
                        $SiteSettings->save($row);
                    }
                }
            }

            // Handle text/textarea fields
            $data = (array)($this->request->getData('site_settings') ?? []);
            foreach ($data as $key => $value) {
                if (!isset($this->homeFields[$key])) {
                    continue;
                }
                $row = $SiteSettings->find()->where(['setting_key' => $key])->first();
                if ($row) {
                    $row->setting_value = $value;
                    $SiteSettings->save($row);
                }
            }
            $this->Flash->success('Homepage content updated.');
            return $this->redirect(['action' => 'index']);
        }

        $settings = [];
        foreach (array_keys($this->homeFields) as $key) {
            $row = $SiteSettings->find()->where(['setting_key' => $key])->first();
            $settings[$key] = $row ? (string)$row->setting_value : $this->homeFields[$key]['default'];
        }

        $this->set([
            'fields'   => $this->homeFields,
            'settings' => $settings,
        ]);
    }
}


