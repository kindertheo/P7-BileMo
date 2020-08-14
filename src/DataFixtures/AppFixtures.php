<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        for($i= 0;$i < 50; $i++){
            $product = new Product();

            $product->setName($faker->word())
                ->setDescription($faker->paragraph())
                ->setPrice($faker->randomNumber(2));

            $manager->persist($product);
        }

        $clients= [] ;

        for($i = 0; $i < 5; $i++){
        $client = new Client();
        $client->setName($faker->word());

        $manager->persist($client);

        $clients[] = $client;
        }


        for($i = 0; $i < 20; $i++){
            $user = new User();


            $user->setName($faker->name)
                ->setEmail($faker->email)
                ->setRole("user")
                ->setPassword($this->encoder->encodePassword($user, "password"))
                ->setClient($clients[random_int(0, count($clients) - 1)]);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
