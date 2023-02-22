<?php

namespace App\Tests\Controller;

use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @coversDefaultClass \App\Controller\HelloController
 * @covers ::index()
 */
class HelloControllerTest extends WebTestCase
{
    public static function getValidNames(): Generator
    {
        yield 'default' => [
            'uri'          => '/hello',
            'expectedName' => 'Adrien',
        ];

        yield 'name "Adrien"' => [
            'uri'          => '/hello/Adrien',
            'expectedName' => 'Adrien',
        ];

        yield 'name "Louise"' => [
            'uri'          => '/hello/Louise',
            'expectedName' => 'Louise',
        ];
    }

    /**
     * @dataProvider getValidNames
     *
     * @group smoke
     */
    public function testPageAndCorrectNameAreDisplayed(string $uri, string $expectedName): void
    {
        $client = static::createClient();
        $client->request('GET', $uri);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString("Hello {$expectedName} !", $client->getResponse()->getContent());
    }
}
