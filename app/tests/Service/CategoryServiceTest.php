<?php
/**
 * Category service tests.
 */

namespace App\Tests\Service;

use App\Entity\Category;

use App\Service\CategoryService;
use App\Service\CategoryServiceInterface;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CategoryServiceTest.
 */
class CategoryServiceTest extends KernelTestCase
{
    /**
     * Category service.
     *
     * @var CategoryService|null
     */
    private ?CategoryServiceInterface $categoryService;

    /**
     * Category repository.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Set up test.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->categoryService = $container->get(CategoryService::class);
    }

    /**
     * Test find one by id.
     *
     * @throws NonUniqueResultException
     */
    public function testFindOneById(): void
    {
        // given
        $expectedCategoryTitle = 'test_cat_service';
        $category = new Category();
        $category->setTitle($expectedCategoryTitle);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        // when
        $resultCategory = $this->categoryService->findOneById($category->getId());

        // then
        $this->assertEquals($expectedCategoryTitle, $resultCategory->getTitle());
    }

}
