<?php

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\Note;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\NoteRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NoteControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/note';

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
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value,], 'test_note__admin@example.com');
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test show single note.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowSingleNote(): void
    {
        // given
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value,], 'test_show_note@example.com');
        $this->httpClient->loginUser($adminUser);

        $expectedNote = new Note();
        $expectedNote->setTitle('Test Note 1');
        $expectedNote->setContent('Content');
        $expectedNote->setCreatedAt(new \DateTimeImmutable('now'));
        $expectedNote->setUpdatedAt(new \DateTimeImmutable('now'));
        $expectedNote->setSlug('test-note-1');
        $expectedNote->setUser($adminUser);
        $expectedNote->setCategory($this->createCategory('Test Category 15'));
        $noteRepository = static::getContainer()->get(NoteRepository::class);
        $noteRepository->save($expectedNote);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $expectedNote->getId());
        $result = $this->httpClient->getResponse();

        // then
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertSelectorTextContains('html', $expectedNote->getTitle());
    }

    /**
     * Test create note.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testCreateNote(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value], 'test_note_create@example.com');
        $this->httpClient->loginUser($user);

        $expectedNoteTitle = 'TestNoteCreated';
        $category = $this->createCategory('Test Category 14');
        $noteRepository = static::getContainer()->get(NoteRepository::class);
        $this->httpClient->request(
            'GET', self::TEST_ROUTE . '/create'
        );

        // when
        $this->httpClient->submitForm(
            'Utwórz',
            [
                'note' => [
                    'title' => $expectedNoteTitle,
                    'category' => $category->getId(),
                    'content' => 'TestNoteCreated'
                ]
            ]
        );

        // then
        $savedNote = $noteRepository->findOneByTitle($expectedNoteTitle);
        $this->assertEquals($expectedNoteTitle, $savedNote->getTitle());

        $result = $this->httpClient->getResponse();
        $this->assertEquals(302, $result->getStatusCode());
    }


    /**
     * Test edit note.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditNote(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value], 'test_note_edit@example.com');
        $this->httpClient->loginUser($user);

        $noteRepository = static::getContainer()->get(NoteRepository::class);
        $testNote = new Note();
        $testNote->setTitle('edited note');
        $testNote->setCreatedAt(new \DateTimeImmutable('now'));
        $testNote->setUpdatedAt(new \DateTimeImmutable('now'));
        $testNote->setSlug('edited-note');
        $testNote->setCategory($this->createCategory('Test Category 13'));
        $testNote->setContent('Content');
        $testNote->setUser($user);
        $noteRepository->save($testNote);
        $testNoteId = $testNote->getId();
        $expectedNewNoteTitle = 'test note edit';

        $this->httpClient->request(
            'GET', self::TEST_ROUTE . '/' .
            $testNoteId . '/edit'
        );

        // when
        $this->httpClient->submitForm(
            'Edytuj',
            ['note' => ['title' => $expectedNewNoteTitle]]
        );

        // then
        $savedNote = $noteRepository->findOneById($testNoteId);
        $this->assertEquals($expectedNewNoteTitle, $savedNote->getTitle());
    }

    /**
     * Test delete note.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testDeleteNote(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value], 'test_note_delete@example.com');
        $this->httpClient->loginUser($user);

        $noteRepository = static::getContainer()->get(NoteRepository::class);
        $testNote = new Note();
        $testNote->setTitle('TestNoteDelete');
        $testNote->setCreatedAt(new \DateTimeImmutable('now'));
        $testNote->setUpdatedAt(new \DateTimeImmutable('now'));
        $testNote->setContent('TestNoteDelete');
        $testNote->setCategory($this->createCategory('Test Category 12'));
        $testNote->setUser($user);
        $noteRepository->save($testNote);
        $testNoteId = $testNote->getId();

        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $testNoteId . '/delete');

        // when
        $this->httpClient->submitForm(
            'Usuń'
        );

        // then
        $this->assertNull($noteRepository->findOneByTitle('TestNoteDelete'));
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

    private function createCategory($name): Category
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