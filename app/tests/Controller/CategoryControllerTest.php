<?php
/**
 * Category Controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\Note;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class CategoryControllerTest.
 */
class CategoryControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/category';

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
        $expectedStatusCode = 302;

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test index route for admin user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException|\Exception
     */
    public function testIndexRouteAdminUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, ], 'test_category__admin@example.com');
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test show single category.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowCategory(): void
    {
        // given
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value], 'test_show_category@example.com');
        $this->httpClient->loginUser($adminUser);

        $expectedCategory = new Category();
        $expectedCategory->setTitle('Test 2 category');
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($expectedCategory);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedCategory->getId());
        $result = $this->httpClient->getResponse();

        // then
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertSelectorTextContains('html h1', $expectedCategory->getTitle());
    }

    /**
     * Test create category.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testCreateCategory(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value], 'test_category_create@example.com');
        $this->httpClient->loginUser($user);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/create');

        // when
        $this->httpClient->submitForm(
            'Utwórz',
            ['category' => ['title' => 'Test Category']]
        );

        // then
        $this->assertEquals(302, $this->httpClient->getResponse()->getStatusCode());
    }

    /**
     * Test edit category.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditCategory(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value], 'test_category_edit@example.com');
        $this->httpClient->loginUser($user);

        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $testCategory = new Category();
        $testCategory->setTitle('edited category');
        $testCategory->setCreatedAt(new \DateTimeImmutable('now'));
        $testCategory->setUpdatedAt(new \DateTimeImmutable('now'));
        $testCategory->setSlug('edited-category');
        $categoryRepository->save($testCategory);
        $testCategoryId = $testCategory->getId();
        $expectedNewCategoryTitle = 'test category edit';

        $this->httpClient->request(
            'GET', self::TEST_ROUTE.'/'.
            $testCategoryId.'/edit'
        );

        // when
        $this->httpClient->submitForm(
            'Edytuj',
            ['category' => ['title' => $expectedNewCategoryTitle]]
        );

        // then
        $savedCategory = $categoryRepository->findOneById($testCategoryId);
        $this->assertEquals($expectedNewCategoryTitle, $savedCategory->getTitle());
    }

    /**
     * Test delete category.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testDeleteCategory(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value], 'test_category_delete@example.com');
        $this->httpClient->loginUser($user);

        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $testCategory = new Category();
        $testCategory->setTitle('TestCategoryCreated');
        $testCategory->setCreatedAt(new \DateTimeImmutable('now'));
        $testCategory->setUpdatedAt(new \DateTimeImmutable('now'));
        $categoryRepository->save($testCategory);
        $testCategoryId = $testCategory->getId();

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testCategoryId.'/delete');

        // when
        $this->httpClient->submitForm(
            'Usuń'
        );

        // then
        $this->assertNull($categoryRepository->findOneByTitle('TestCategoryCreated'));
    }

    /**
     * Test if category cant be deleted.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testCantDeleteCategory(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value], 'test_category_can_delete@example.com');
        $this->httpClient->loginUser($user);

        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $testCategory = new Category();
        $testCategory->setTitle('TestCategoryCreated2');
        $testCategory->setCreatedAt(new \DateTimeImmutable('now'));
        $testCategory->setUpdatedAt(new \DateTimeImmutable('now'));
        $categoryRepository->save($testCategory);
        $testCategoryId = $testCategory->getId();

        $this->createNote($user, $testCategory);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testCategoryId.'/delete');

        // then
        $this->assertEquals(302, $this->httpClient->getResponse()->getStatusCode());
        $this->assertNotNull($categoryRepository->findOneByTitle('TestCategoryCreated2'));
    }

    /**
     * Create user.
     *
     * @param array $roles User roles
     *
     * @return User User entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    private function createUser(array $roles, $email): User
    {
        $passwordHasher = static::getContainer()->get('security.password_hasher');
        $user = new User();
        $user->setEmail($email);
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setRoles($roles);
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                'p@55w0rd'
            )
        );
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($user, true);

        return $user;
    }

    /**
     * Create Note.
     *
     * @param User $user User entity
     * @param Category $testCategory Category entity
     */
    private function createNote(User $user, Category $testCategory): void
    {
        $note = new Note();
        $note->setTitle('Test Note');
        $note->setCreatedAt(new \DateTimeImmutable('now'));
        $note->setUpdatedAt(new \DateTimeImmutable('now'));
        $note->setSlug('test-note');
        $note->setUser($user);

        $note->setCategory($testCategory);
        $note->setContent('Test Note Content');

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($note);
        $entityManager->flush();

    }
}
