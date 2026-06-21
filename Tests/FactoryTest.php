<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Platform\Bridge\ElevenLabs\Tests;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Bridge\ElevenLabs\Factory;
use Symfony\AI\Platform\Bridge\ElevenLabs\ModelCatalog;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\ScopingHttpClient;

final class FactoryTest extends TestCase
{
    #[TestWith(['https://api.elevenlabs.io/v1/'])]
    #[TestWith(['https://api.elevenlabs.io/v1'])]
    public function testEndpointTrailingSlashIsToleratedForRelativePathResolution(string $endpoint)
    {
        $httpClient = new MockHttpClient(function (string $method, string $url): JsonMockResponse {
            $this->assertSame('https://api.elevenlabs.io/v1/models', $url);

            return new JsonMockResponse([]);
        });

        $provider = Factory::createProvider($endpoint, apiKey: 'foo', httpClient: $httpClient);
        $provider->getModelCatalog()->getModels();
    }

    public function testProviderCanBeCreatedWithHttpClientAndRequiredInfos()
    {
        $provider = Factory::createProvider(apiKey: 'foo', httpClient: HttpClient::create());

        $this->assertInstanceOf(ModelCatalog::class, $provider->getModelCatalog());
    }

    public function testProviderCanBeCreatedWithScopingHttpClient()
    {
        $provider = Factory::createProvider(httpClient: ScopingHttpClient::forBaseUri(HttpClient::create(), 'https://api.elevenlabs.io/v1/', [
            'headers' => [
                'xi-api-key' => 'bar',
            ],
        ]));

        $this->assertInstanceOf(ModelCatalog::class, $provider->getModelCatalog());
    }
}
