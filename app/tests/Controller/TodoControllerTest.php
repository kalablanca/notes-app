<?php

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\Todo;
use App\Entity\TodoItem;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\TodoItemRepository;
use App\Repository\TodoRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TodoControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/todo';

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
     * Test index route for admin user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testIndexRouteAdminUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value,], 'test_todo__admin@example.com');
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test show single todo.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowSingleTodo(): void
    {
        // given
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value,], 'test_show_todo@example.com');
        $this->httpClient->loginUser($adminUser);

        $expectedTodo = new Todo();
        $expectedTodo->setTitle('Test Todo 1');
        $expectedTodo->setCreatedAt(new \DateTimeImmutable('now'));
        $expectedTodo->setUpdatedAt(new \DateTimeImmutable('now'));
        $expectedTodo->setSlug('test-todo-1');
        $expectedTodo->setUser($adminUser);
        $todoRepository = static::getContainer()->get(TodoRepository::class);
        $todoRepository->save($expectedTodo);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $expectedTodo->getId());
        $result = $this->httpClient->getResponse();

        // then
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertSelectorTextContains('html', $expectedTodo->getTitle());
    }

    /**
     * Test create todo.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testCreateTodo(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value], 'test_todo_create@example.com');
        $this->httpClient->loginUser($user);

        $expectedTodoTitle = 'TestTodoCreated';
        $category = $this->createCategory('Test 2');
        $todoRepository = static::getContainer()->get(TodoRepository::class);
        $this->httpClient->request(
            'GET', self::TEST_ROUTE . '/create'
        );

        // when
        $this->httpClient->submitForm(
            'Utwórz',
            [
                'todo' => [
                    'title' => $expectedTodoTitle,
                ]
            ]
        );

        // then
        $savedTodo = $todoRepository->findOneByTitle($expectedTodoTitle);
        $this->assertEquals($expectedTodoTitle, $savedTodo->getTitle());

        $result = $this->httpClient->getResponse();
        $this->assertEquals(302, $result->getStatusCode());
    }

    /**
     * Test edit todo.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditTodo(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value], 'test_todo_edit@exampl.com');
        $this->httpClient->loginUser($user);

        $todoRepository = static::getContainer()->get(TodoRepository::class);
        $testTodo = new Todo();
        $testTodo->setTitle('TestTodoEdit');
        $testTodo->setCreatedAt(new \DateTimeImmutable('now'));
        $testTodo->setUpdatedAt(new \DateTimeImmutable('now'));
        $testTodo->setSlug('test-todo-edit');
        $testTodo->setUser($user);
        $todoRepository->save($testTodo);
        $testTodoId = $testTodo->getId();
        $expectedNewTodoTitle = 'test todo new';

        $this->httpClient->request(
            'GET', self::TEST_ROUTE . '/' . $testTodoId . '/edit'
        );

        // when
        $this->httpClient->submitForm(
            'Edytuj',
            ['todo' => ['title' => $expectedNewTodoTitle]]
        );

        // then
        $savedTodo = $todoRepository->findOneBy(['title' => $expectedNewTodoTitle]);
        $this->assertEquals($expectedNewTodoTitle, $savedTodo->getTitle());
    }

    /**
     * Test delete todo.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testDeleteTodo(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value], 'test_todo_delete@example.com');
        $this->httpClient->loginUser($user);

        $todoRepository = static::getContainer()->get(TodoRepository::class);
        $testTodo = new Todo();
        $testTodo->setTitle('TestTodoDelete');
        $testTodo->setCreatedAt(new \DateTimeImmutable('now'));
        $testTodo->setUpdatedAt(new \DateTimeImmutable('now'));
        $testTodo->setUser($user);
        $todoRepository->save($testTodo);
        $testTodoId = $testTodo->getId();

        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $testTodoId . '/delete');

        // when
        $this->httpClient->submitForm(
            'Usuń'
        );

        // then
        $this->assertNull($todoRepository->findOneByTitle('TestTodoDelete'));
    }

    /**
     * Test delete todo that contains todo items.
     */
    public function testDeleteTodoWithTodoItems(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value], 'test_delete_todo_with_items@example.com');
        $this->httpClient->loginUser($user);
        $todoRepository = static::getContainer()->get(TodoRepository::class);
        $todoItemRepository = static::getContainer()->get(TodoItemRepository::class);

        $testTodo = new Todo();
        $testTodo->setTitle('TestTodoDelete');
        $testTodo->setCreatedAt(new \DateTimeImmutable('now'));
        $testTodo->setUpdatedAt(new \DateTimeImmutable('now'));
        $testTodo->setUser($user);
        $todoRepository->save($testTodo);

        $testTodoItem = new TodoItem();
        $testTodoItem->setTitle('TestTodoItemDelete');
        $testTodoItem->setCreatedAt(new \DateTimeImmutable('now'));
        $testTodoItem->setUpdatedAt(new \DateTimeImmutable('now'));
        $testTodoItem->setTodo($testTodo);
        $todoItemRepository->save($testTodoItem);

        $testTodoId = $testTodo->getId();

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $testTodoId . '/delete');

        // then
        $this->assertNotNull($todoRepository->findOneByTitle('TestTodoDelete'));
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
        $user->setFirstName('User');
        $user->setLastName('Name');
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
     * Create Category.
     *
     * @param string $name
     * @return Category
     */
    private function createCategory(string $name): Category
    {
        $category = new Category();
        $category->setTitle($name);
        $category->setCreatedAt(new \DateTimeImmutable('now'));
        $category->setUpdatedAt(new \DateTimeImmutable('now'));
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($category);

        return $category;
    }
}