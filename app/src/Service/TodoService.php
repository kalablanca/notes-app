<?php
/**
 * Todo service.
 */

namespace App\Service;

use App\Entity\Todo;
use App\Entity\User;
use App\Repository\TodoItemRepository;
use App\Repository\TodoRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class TodoService.
 */
class TodoService implements TodoServiceInterface
{
    /**
     * Todo repository.
     */
    private TodoRepository $todoRepository;

    /**
     * TodoItem repository.
     */
    private TodoItemRepository $todoItemRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * TodoService constructor.
     *
     * @param TodoRepository     $todoRepository     Todo repository
     * @param PaginatorInterface $paginator          Paginator
     * @param TodoItemRepository $todoItemRepository TodoItem repository
     */
    public function __construct(TodoRepository $todoRepository, PaginatorInterface $paginator, TodoItemRepository $todoItemRepository)
    {
        $this->todoRepository = $todoRepository;
        $this->todoItemRepository = $todoItemRepository;
        $this->paginator = $paginator;
    }

    /**
     * Get paginated list.
     *
     * @param int  $page Page number
     * @param User $user User entity
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, User $user): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->todoRepository->queryByUser($user),
            $page,
            TodoRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save entity.
     *
     * @param Todo $todo Todo entity
     */
    public function save(Todo $todo): void
    {
        $this->todoRepository->save($todo);
    }

    /**
     * Delete entity.
     *
     * @param Todo $todo Todo entity
     */
    public function delete(Todo $todo): void
    {
        $this->todoRepository->delete($todo);
    }

    /**
     * Find todo items by todo.
     *
     * @param int  $page Page number
     * @param Todo $todo Todo entity
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getTodoItemsByTodoPaginatedList(int $page, Todo $todo): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->todoItemRepository->queryTodoItemsByTodo($todo),
            $page,
            TodoRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Can todo be deleted?
     *
     * @param Todo $todo Todo entity
     *
     * @return bool Result
     *
     * @throws NonUniqueResultException|NoResultException
     */
    public function canBeDeleted(Todo $todo): bool
    {
        $result = $this->todoItemRepository->countByTodo($todo);

        return !($result > 0);
    }

    /**
     * Find one by id.
     *
     * @param int $todoId Todo id
     *
     * @return Todo|null Todo entity
     */
    public function findOneById(int $todoId): ?Todo
    {
        return $this->todoRepository->findOneBy(['id' => $todoId]);
    }
}
