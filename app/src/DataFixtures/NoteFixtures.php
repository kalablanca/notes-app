<?php
/**
 * Note fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Note;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class NoteFixtures.
 */
class NoteFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
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

        $this->createMany(100, 'notes', function (int $i) {
            $note = new Note();
            $note->setTitle($this->faker->sentence);
            $note->setSlug($this->faker->word);
            $note->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $note->setUpdatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $note->setContent($this->faker->paragraphs(
                $this->faker->numberBetween(4, 8),
                true
            ));

            /** @var Category $category */
            $category = $this->getRandomReference('categories');
            $note->setCategory($category);

            /** @var User $user */
            $user = $this->getRandomReference('admins');
            $note->setUser($user);

            return $note;
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
        return [CategoryFixtures::class, UserFixtures::class];
    }
}
