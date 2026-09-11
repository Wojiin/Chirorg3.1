<?php

namespace App\Tests\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

final class HealthApiTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    public function testHealthEndpointIsPublicAndChecksTheDatabase(): void
    {
        $response = static::createClient()->request('GET', '/api/health');

        self::assertResponseIsSuccessful();
        self::assertSame(['etat' => 'disponible'], $response->toArray());
    }
}
