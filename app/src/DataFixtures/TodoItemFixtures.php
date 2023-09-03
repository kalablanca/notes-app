<?php
/**
 * TodoItem fixtures.
 */

namespace App\DataFixtures;

use App\Entity\todoItem;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class TodoItemFixtures.
 */
class TodoItemFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullPropertyFetch
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }

        $this->createMany(200, 'todoItems', function (int $i) {
            $todoItem = new todoItem();
            $todoItem->setTitle($this->faker->sentence);
            $todoItem->setSlug($this->faker->word);
            $todoItem->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $todoItem->setUpdatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $todoItem->setTodo(
                $this->getRandomReference('todos')
            );
            $todoItem->setIsDone($this->faker->boolean(20));

            return $todoItem;
        });
        $this->manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     *
     * @psalm-return array{0: CategoryFixtures::class}
     */
    public function getDependencies(): array
    {
        return [TodoFixtures::class];
    }
}
