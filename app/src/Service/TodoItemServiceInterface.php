<?php
/**
 * TodoItem service interface.
 */

namespace App\Service;

use App\Entity\Todo;
use App\Entity\TodoItem;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface TodoItemServiceInterface.
 */
interface TodoItemServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Save entity.
     *
     * @param TodoItem $todoItem TodoItem entity
     */
    public function save(TodoItem $todoItem): void;

    /**
     * Delete entity.
     *
     * @param TodoItem $todoItem TodoItem entity
     *
     * @return void
     */
    public function delete(TodoItem $todoItem): void;

    /**
     * Create entity.
     *
     * @param TodoItem $todoItem
     * @param int $todoId
     *
     * @return void
     */
    public function create(TodoItem $todoItem, int $todoId): void;
}
