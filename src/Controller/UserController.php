<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{

    /**
     * @Route("/user/{clientName}", name="list_user", methods={"GET"})
     * @param SerializerInterface $serializer
     * @param $userId
     * @param $clientName
     * @return Response
     */
    /*TODO AJOUTER LE NOM DU CLIENT*/
    public function listUserByClient(SerializerInterface $serializer, $clientName)
    {
        $manager = $this->getDoctrine()->getManager();
        $clientId = $manager->getRepository(Client::class)->findOneBy(['name' => $clientName])->getId();

        $data = $manager->getRepository(User::class)
            ->findBy(
                [
                    "client" => $clientId
                ]
            );

        $data = $serializer->serialize($data, "json", ['groups' => 'user']);

        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }


    /**
     * @Route("/user/{clientName}/{userId}", name="show_user", methods={"GET"})
     * @ParamConverter()
     * @param SerializerInterface $serializer
     * @param $userId
     * @param $clientName
     * @return Response
     */
    public function showUser(SerializerInterface $serializer, $userId, $clientName){

        $manager = $this->getDoctrine()->getManager();
        $clientId = $manager->getRepository(Client::class)->findOneBy(['name' => $clientName])->getId();

        $data = $manager->getRepository(User::class)
            ->findBy(
                [
                    'id' => $userId,
                    "client" => $clientId
                ]
            );

        $data = $serializer->serialize($data, 'json', ['groups' => 'user']);

        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/user/add", name="add_user", methods={"POST"})
     * @ParamConverter("product")
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function addUser(Request $request, SerializerInterface $serializer){
        $data = $request->getContent();
        $user = $serializer->deserialize($data, User::class,'json', ['groups'=> 'user']);
        $data = json_decode($data, true);
        $clientId = $data['client'];

        $client = $this->getDoctrine()->getManager()->getRepository(Client::class)->findOneBy(['id' => $clientId]);
        $user->setClient($client);

        //$user = $serializer->deserialize($data, User::class, 'json');

        $manager = $this->getDoctrine()->getManager();

        $manager->persist($user);
        $manager->flush();

        $response = new Response($user);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/user/delete/{id}", name="delete_user", methods={"DELETE"})
     *
     */
    public function deleteUser(User $user){
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($user);
        $manager->flush();

        return new Response("", 204);
    }


}
