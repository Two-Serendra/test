<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;
use App\Models\Events;
class GenerateSitemap extends Command
{
    protected $signature = 'generate:sitemap';
    protected $description = 'Generate the sitemap.xml file';

    public function handle()
    {
        $sitemap = SitemapGenerator::create('https://twoserendra.com')
            ->getSitemap();

        // Add all your custom routes manually
        $sitemap->add(Url::create('/home'));
        $sitemap->add(Url::create('/about'));
        $sitemap->add(Url::create('/services'));
        $sitemap->add(Url::create('/contact'));
        $sitemap->add(Url::create('/downloadables')); // typo? maybe /downloadables
        $sitemap->add(Url::create('/sections'));
        $sitemap->add(Url::create('/maps'));
        $sitemap->add(Url::create('/events'));

        // If you want to include individual events dynamically
        $events = Events::all(); // Replace with your actual Event model
        foreach ($events as $event) {
            $sitemap->add(Url::create("/events/{$event->id}"));
        }

        // Save the sitemap to public folder
        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('✅ Sitemap generated successfully!');
    }
}

