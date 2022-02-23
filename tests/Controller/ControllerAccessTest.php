<?php

namespace App\Tests\Controller;

use DirectoryIterator;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Route;

/**
 * Controller "smoke" test
 */
class ControllerAccessTest extends WebTestCase
{
    /**
     * @var array<string, array<string, array<string, int>>>
     */
    private array $exceptions
        = [
            'default'                  => [
                'statusCodes' => ['GET' => 200],
            ],
            'login'                    => [
                'statusCodes' => ['GET' => 200],
            ],
            'connect_google_api_token' => [
                'statusCodes' => ['GET' => 200],
            ],
        ];

    /**
     * @throws Exception
     */
    public function testRoutes(): void
    {
        $client = static::createClient();
        $routeLoader = static::bootKernel()->getContainer()
            ->get('routing.loader');

        foreach (
            new DirectoryIterator(__DIR__.'/../../src/Controller') as $item
        ) {
            if (
                $item->isDot()
                || $item->isDir()
                || in_array(
                    $item->getBasename(),
                    ['.gitignore', 'GoogleController.php']
                )
            ) {
                continue;
            }

            $routerClass = 'App\Controller\\'.basename(
                    $item->getBasename(),
                    '.php'
                );
            $routes = $routeLoader->load($routerClass)->all();

            $this->processRoutes($routes, $client);
        }
    }

    /**
     * @param array<Route> $routes
     */
    private function processRoutes(array $routes, KernelBrowser $browser): void
    {
        foreach ($routes as $routeName => $route) {
            $defaultId = 1;
            $expectedStatusCodes = [];
            if (array_key_exists($routeName, $this->exceptions)
                && array_key_exists(
                    'statusCodes',
                    $this->exceptions[$routeName]
                )
            ) {
                $expectedStatusCodes = $this->exceptions[$routeName]['statusCodes'];
            }

            $methods = $route->getMethods();

            if (!$methods) {
                echo sprintf(
                        'No methods set in controller "%s"',
                        $route->getPath()
                    ).PHP_EOL;
                $methods = ['GET'];
            }

            $path = str_replace('{id}', (string)$defaultId, $route->getPath());
            $out = false;
            foreach ($methods as $method) {
                $expectedStatusCode = 302;
                if (array_key_exists($method, $expectedStatusCodes)) {
                    $expectedStatusCode = $expectedStatusCodes[$method];
                }

                $browser->request($method, $path);

                self::assertEquals(
                    $expectedStatusCode,
                    $browser->getResponse()->getStatusCode(),
                    sprintf(
                        'failed: %s (%s) with method: %s',
                        $routeName,
                        $path,
                        $method
                    )
                );
            }
        }
    }
}
