<?php
/**
 * TodoItem service.
 */

namespace App\Service;

use App\Entity\Todo;
use App\Entity\TodoItem;
use App\Repository\TodoItemRepository;
use App\Repository\TodoRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class TodoItemService.
 */
class TodoItemService implements TodoItemServiceInterface
{
    /**
     * TodoItem repository.
     */
    private TodoItemRepository $todoItemRepository;

    /**
     * Todo repository.
     */
    private TodoRepository $todoRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * TodoItemService constructor.
     *
     * @param TodoItemRepository $todoItemRepository TodoItem repository
     * @param TodoRepository     $todoRepository     Todo repository
     * @param PaginatorInterface $paginator          Paginator
     */
    public function __construct(TodoItemRepository $todoItemRepository, TodoRepository $todoRepository, PaginatorInterface $paginator)
    {
        $this->todoItemRepository = $todoItemRepository;
        $this->todoRepository = $todoRepository;
        $this->paginator = $paginator;
    }

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->todoItemRepository->queryAll(),
            $page,
            TodoItemRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save entity.
     *
     * @param TodoItem $todoItem TodoItem entity
     */
    public function save(TodoItem $todoItem): void
    {
        $this->todoItemRepository->save($todoItem);
    }

    /**
     * Delete entity.
     *
     * @param TodoItem $todoItem TodoItem entity
     */
    public function delete(TodoItem $todoItem): void
    {
        $this->todoItemRepository->delete($todoItem);
    }

    /**
     * Create entity.
     *
     * @param TodoItem $todoItem TodoItem entity
     * @param int      $todoId   TodoId
     */
    public function create(TodoItem $todoItem, int $todoId): void
    {
        $todo = $this->todoRepository->findOneBy(['id' => $todoId]);
        $todoItem->setTodo($todo);
        $this->todoItemRepository->save($todoItem);
    }
}
