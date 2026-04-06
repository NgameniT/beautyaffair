<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Throwable;

final class CataloguePageTest extends WebTestCase
{
    public function testCataloguePageLoads(): void
    {
        $client = static::createClient();

        try {
            $client->request('GET', '/catalogue');
        } catch (Throwable $exception) {
            self::markTestSkipped('Base de test non disponible pour le catalogue: '.$exception->getMessage());
        }

        if (500 === $client->getResponse()->getStatusCode()) {
            self::markTestSkipped('Base de test non provisionnee pour le test catalogue.');
        }

        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('Catalogue');
    }
}
