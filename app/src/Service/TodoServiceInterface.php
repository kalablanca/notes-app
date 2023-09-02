<?php
/**
 * Todo service interface.
 */

namespace App\Service;

use App\Entity\Todo;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface TodoServiceInterface.
 */
interface TodoServiceInterface
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
     * @param Todo $todo Todo entity
     */
    public function save(Todo $todo): void;

    /**
     * Delete entity.
     *
     * @param Todo $todo Todo entity
     */
    public function delete(Todo $todo): void;

    /**
     * Find todo items by todo.
     *
     * @param int $page
     * @param Todo $todo
     * @return mixed
     */
    public function getTodoItemsByTodoPaginatedList(int $page, Todo $todo): PaginationInterface;
}
