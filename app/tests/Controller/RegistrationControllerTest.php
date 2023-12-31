<?php
/**
 * Registration controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Psr\Container\ContainerExceptionInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class RegistrationControllerTest.
 */
class RegistrationControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/register';

    /**
     * Test client.
     */
    private KernelBrowser $httpClient;

    /**
     * Set up tests.
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
    }

    /**
     * Test index route for anonymous user.
     */
    public function testIndexRouteAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 200;

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test register action.
     *
     * @throws ContainerExceptionInterface
     */
    public function testRegister(): void
    {
        // given
        $expectedEmail = 'register_user1@example.com';

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $this->httpClient->submitForm(
            'Rejestruj',
            [
                'registration' => [
                    'email' => $expectedEmail,
                    'password' => 'p@55w0rdAAA',
                    'firstName' => 'John',
                    'lastName' => 'Doer',
                ],
            ]
        );

        // then
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail($expectedEmail);
        $this->assertNotNull($user);
    }
}
