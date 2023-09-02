<?php
/**
 * Todo fixtures.
 */

namespace App\DataFixtures;

use App\Entity\todo;
use DateTimeImmutable;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class TodoFixtures.
 */
class TodoFixtures extends AbstractBaseFixtures
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

        $this->createMany(50, 'todos', function (int $i) {
            $todo = new todo();
            $todo->setTitle($this->faker->word);
            $todo->setSlug($this->faker->word);
            $todo->setCreatedAt(
                DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $todo->setUpdatedAt(
                DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );



            return $todo;
        });
        $this->manager->flush();
    }
}
