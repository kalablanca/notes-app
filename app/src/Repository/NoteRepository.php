<?php
/**
 * Note repository.
 */

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Note;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class NoteRepository.
 *
 * @extends ServiceEntityRepository<Note>
 *
 * @method Note|null find($id, $lockMode = null, $lockVersion = null)
 * @method Note|null findOneBy(array $criteria, array $orderBy = null)
 * @method Note[]    findAll()
 * @method Note[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @psalm-supress LessSpecificImplementedReturnType
 */
class NoteRepository extends ServiceEntityRepository
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
        parent::__construct($registry, Note::class);
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
                'partial note.{id, title, createdAt, updatedAt}',
                'partial category.{id, title}',
            )
            ->join('note.category', 'category')
            ->orderBy('note.updatedAt', 'DESC');

        return $queryBuilder;
    }

    /**
     * Query all records by category.
     *
     * @param Category $category Category entity
     *
     * @return QueryBuilder Query builder
     */
    public function queryByCategory(Category $category): QueryBuilder
    {
        $queryBuilder = $this->getOrCreateQueryBuilder();

        $queryBuilder
            ->select(
                'partial note.{id, title, createdAt, updatedAt}',
                'partial category.{id, title}',
            )
            ->join('note.category', 'category')
            ->where('note.category = :category')
            ->setParameter(':category', $category)
            ->orderBy('note.updatedAt', 'DESC');

        return $queryBuilder;
    }

    /**
     * Count notes by category.
     *
     * @param Category $category Category
     *
     * @return int Number of tasks in category
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countByCategory(Category $category): int
    {
        $qb = $this->getOrCreateQueryBuilder();

        return $qb->select($qb->expr()->countDistinct('note.id'))
            ->where('note.category = :category')
            ->setParameter(':category', $category)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Save record.
     *
     * @param Note $note Note entity
     */
    public function save(Note $note): void
    {
        $this->_em->persist($note);
        $this->_em->flush();
    }

    /**
     * Delete record.
     *
     * @param Note $note Note entity
     */
    public function delete(Note $note): void
    {
        $this->_em->remove($note);
        $this->_em->flush();
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
        return $queryBuilder ?? $this->createQueryBuilder('note');
    }
}
