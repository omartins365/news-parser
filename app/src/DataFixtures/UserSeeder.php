<?php
namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserSeeder extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        // Create an admin user
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordEncoder->encodePassword(
            $admin,
            'password'
        ));
        $manager->persist($admin);

        // Create a moderator user
        $moderator = new User();
        $moderator->setEmail('moderator@example.com');
        $moderator->setRoles(['ROLE_MODERATOR']);
        $moderator->setPassword($this->passwordEncoder->encodePassword(
            $moderator,
            'password'
        ));
        $manager->persist($moderator);

        $manager->flush();
    }
}
