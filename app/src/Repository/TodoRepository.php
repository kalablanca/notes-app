<?php
/**
 * Todo repository.
 */

namespace App\Repository;

use App\Entity\Todo;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class TodoRepository.
 *
 * @extends ServiceEntityRepository<Todo>
 *
 * @method Todo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Todo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Todo[]    findAll()
 * @method Todo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @psalm-supress LessSpecificImplementedReturnType
 */
class TodoRepository extends ServiceEntityRepository
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
        parent::__construct($registry, Todo::class);
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
                'partial todo.{id, title, createdAt, updatedAt}',
            )
            ->orderBy('todo.updatedAt', 'DESC');

        return $queryBuilder;
    }

    /**
     * Query by user.
     *
     * @param User $user User entity
     *
     * @return QueryBuilder Query builder
     */
    public function queryByUser(User $user): QueryBuilder
    {
        $queryBuilder = $this->getOrCreateQueryBuilder();

        $queryBuilder
            ->select(
                'partial todo.{id, title, createdAt, updatedAt}',
            )
            ->where('todo.user = :user')
            ->setParameter('user', $user)
            ->orderBy('todo.updatedAt', 'DESC');

        return $queryBuilder;
    }

    /**
     * Save record.
     *
     * @param Todo $todo Todo entity
     */
    public function save(Todo $todo): void
    {
        $this->_em->persist($todo);
        $this->_em->flush();
    }

    /**
     * Delete record.
     *
     * @param Todo $todo Todo entity
     */
    public function delete(Todo $todo): void
    {
        $this->_em->remove($todo);
        $this->_em->flush();
    }

    /**
     * Find todo by id.
     *
     * @param int $todoId Todo id
     *
     * @return QueryBuilder Query builder
     */
    public function findOneById(int $todoId): QueryBuilder
    {
        $queryBuilder = $this->getOrCreateQueryBuilder();

        $queryBuilder
            ->select(
                'partial todo.{id, title, createdAt, updatedAt}',
            )
            ->where('todo.id = :id')
            ->setParameter('id', $todoId);

        return $queryBuilder;
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
        return $queryBuilder ?? $this->createQueryBuilder('todo');
    }
}
