<?php
/**
 * TodoItem repository.
 */

namespace App\Repository;

use App\Entity\Todo;
use App\Entity\TodoItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class TodoItemRepository.
 *
 * @extends ServiceEntityRepository<TodoItem>
 *
 * @method TodoItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method TodoItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method TodoItem[]    findAll()
 * @method TodoItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @psalm-supress LessSpecificImplementedReturnType
 */
class TodoItemRepository extends ServiceEntityRepository
{
    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in configuration files.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    public const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TodoItem::class);
    }

    /**
     * Query all records.
     *
     * @param array $filters Filters
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(array $filters = []): QueryBuilder
    {
        $queryBuilder = $this->getOrCreateQueryBuilder();

        $queryBuilder
            ->select(
                'partial todoItem.{id, title, createdAt, updatedAt, isDone}',
            )
            ->orderBy('todoItem.updatedAt', 'DESC');

        return $queryBuilder;
    }

    /**
     * Query todo items by todo.
     *
     * @param Todo $todo Todo entity
     *
     * @return QueryBuilder Query builder
     */
    public function queryTodoItemsByTodo(Todo $todo): QueryBuilder
    {
        $queryBuilder = $this->getOrCreateQueryBuilder();

        $queryBuilder
            ->select(
                'partial todoItem.{id, title, createdAt, updatedAt, isDone}',
            )
            ->where('todoItem.todo = :todo')
            ->setParameter('todo', $todo)
            ->orderBy('todoItem.updatedAt', 'DESC');

        return $queryBuilder;
    }

    /**
     * Save record.
     *
     * @param TodoItem $todoItem TodoItem entity
     */
    public function save(TodoItem $todoItem): void
    {
        $this->_em->persist($todoItem);
        $this->_em->flush();
    }

    /**
     * Delete record.
     *
     * @param TodoItem $todoItem TodoItem entity
     */
    public function delete(TodoItem $todoItem): void
    {
        $this->_em->remove($todoItem);
        $this->_em->flush();
    }

    /**
     * Count by todo.
     *
     * @param Todo $todo Todo entity
     *
     * @return int Result
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countByTodo(Todo $todo): int
    {
        $queryBuilder = $this->createQueryBuilder('todoItem');

        return $queryBuilder->select($queryBuilder->expr()->count('todoItem'))
            ->where('todoItem.todo = :todo')
            ->setParameter('todo', $todo)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get or create new query builder.
     *
     * @param QueryBuilder|null $queryBuilder Query builder
     *
     * @return QueryBuilder Query builder
     */
    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('todoItem');
    }
}
