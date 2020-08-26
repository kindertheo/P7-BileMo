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


        $roles = ["ROLE_USER", "ROLE_CLIENT"];


        for($i = 0; $i < 20; $i++){
            $user = new User();


            $user->setName($faker->name)
                ->setEmail($faker->email)
                ->setRole($roles[ random_int(0, count($roles) - 1) ] )
                ->setPassword($this->encoder->encodePassword($user, "password"))
                ->setClient($clients[random_int(0, count($clients) - 1)]);

            $manager->persist($user);
        }
        $userAdmin = new User();
        $userAdmin->setName("Théo Kinder")
            ->setEmail("kinder.theo@gmail.com")
            ->setRole("ROLE_ADMIN")
            ->setPassword($this->encoder->encodePassword($userAdmin, "password"))
            ->setClient($clients[random_int(0, count($clients) - 1)]);
        $manager->persist($userAdmin);


        $standardUser = new User();
        $standardUser->setName("Utilisateur standard")
            ->setEmail("user@user.com")
            ->setPassword($this->encoder->encodePassword($standardUser, "password"))
            ->setClient($clients[random_int(0, count($clients) - 1)])
            ->setRole("ROLE_USER");

        $manager->persist($standardUser);

        $standardClient = new User();
        $standardClient->setName("Client standard")
            ->setEmail("client@client.com")
            ->setPassword($this->encoder->encodePassword($standardClient, "password"))
            ->setClient($clients[random_int(0, count($clients) - 1)])
            ->setRole("ROLE_CLIENT");

        $manager->persist($standardClient);

        $standardAdmin = new User();
        $standardAdmin->setName("Administrateur")
            ->setEmail("admin@admin.com")
            ->setRole("ROLE_ADMIN")
            ->setPassword($this->encoder->encodePassword($standardAdmin, "password"))
            ->setClient($clients[random_int(0, count($clients) - 1)]);
        $manager->persist($standardAdmin);


        $manager->flush();
    }
}
