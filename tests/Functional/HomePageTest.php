<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomePageTest extends WebTestCase
{
    public function testHomePageLoads(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('BiblioConnect');
        self::assertStringContainsString('BiblioConnect', $crawler->filter('body')->text());
    }
}
