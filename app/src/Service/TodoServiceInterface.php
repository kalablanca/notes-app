<?php
/**
 * Todo service interface.
 */

namespace App\Service;

use App\Entity\Todo;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface TodoServiceInterface.
 */
interface TodoServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int  $page Page number
     * @param User $user User entity
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, User $user): PaginationInterface;

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
     * @return mixed
     */
    public function getTodoItemsByTodoPaginatedList(int $page, Todo $todo): PaginationInterface;

    /**
     * Can todo be deleted?
     *
     * @param Todo $todo Todo entity
     */
    public function canBeDeleted(Todo $todo): bool;

    /**
     * Find one by id.
     *
     * @param int $todoId Todo id
     *
     * @return Todo|null Todo entity
     */
    public function findOneById(int $todoId): ?Todo;
}
