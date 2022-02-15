<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use DirectoryIterator;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Admin Controller "smoke" test
 */
class ControllerAdminAccessTest extends WebTestCase
{
    private array $exceptions
        = [
            'default'                  => [
                'statusCodes' => ['GET' => 200],
            ],
            'login'                    => [
                'statusCodes' => ['GET' => 200],
            ],
            'maxfield_show'                => [
                'statusCodes' => ['GET' => 200],
            ],
            'maxfield'                => [
                'statusCodes' => ['GET' => 200, 'POST' => 200],
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

        $user = static::getContainer()->get(UserRepository::class)
            ->findOneByIdentifier('admin');

        $routeLoader = static::getContainer()
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

            $this->processRoutes($routes, $client, $user);
        }
    }

    private function processRoutes(array $routes, KernelBrowser $browser, UserInterface $user): void
    {
        foreach ($routes as $routeName => $route) {
            $defaultId = 1;
            $expectedStatusCodes = [];
            if (array_key_exists($routeName, $this->exceptions)) {
                if (array_key_exists(
                    'statusCodes',
                    $this->exceptions[$routeName]
                )
                ) {
                    $expectedStatusCodes = $this->exceptions[$routeName]['statusCodes'];
                }
                if (array_key_exists('params', $this->exceptions[$routeName])) {
                    $params = $this->exceptions[$routeName]['params'];
                    if (array_key_exists('id', $params)) {
                        $defaultId = $params['id'];
                    }
                }
            }

            $methods = $route->getMethods() ?: ['GET'];
            $path = str_replace('{id}', $defaultId, $route->getPath());
            $out = false;
            foreach ($methods as $method) {
                $expectedStatusCode = 302;
                if (array_key_exists($method, $expectedStatusCodes)) {
                    $expectedStatusCode = $expectedStatusCodes[$method];
                }
                if ($out) {
                    echo sprintf(
                        'Testing: %s - %s Expected: %s ... ',
                        $method,
                        $path,
                        $expectedStatusCode,
                    );
                }

                $browser->loginUser($user);
                $browser->request($method, $path);

                if ($out) {
                    echo sprintf(
                            ' got: %s',
                            $browser->getResponse()->getStatusCode()
                        ).PHP_EOL;
                }

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
