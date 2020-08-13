<?php

namespace App\Controller;

use App\Entity\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ClientController extends AbstractController
{
    /**
     * @Route("/client", name="client")
     */
    public function index()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ClientController.php',
        ]);
    }

    /**
     * @Route("/client/add", name="add_client", methods={"POST"})
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function addClient(Request $request, SerializerInterface $serializer, ValidatorInterface $validator){

        $client = $serializer->deserialize($request->getContent(), Client::class, "json");

        $manager = $this->getDoctrine()->getManager();

        $errors = $validator->validate($client);
        if(count($errors) > 0){
            $errorsSerialized = $serializer->serialize($errors, "json");
            return new Response($errorsSerialized, 400, ["Content-type" => "application/json"]);
        }

        $manager->persist($client);
        $manager->flush();

        $jsonResponse = new JsonResponse(
            $serializer->serialize($client, "json", ['groups' => 'client']),
            JsonResponse::HTTP_CREATED,
            [],
            true
        );
        return $jsonResponse;
    }

    /**
     * @Route("/client/{id}", name="show_client", methods={"GET"})
     * @param Client $client
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function showClient(Client $client, SerializerInterface $serializer){

        $clientJson = $serializer->serialize($client, "json", ['groups' => 'client']);
        $response = new Response($clientJson, 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/client/update/{id}", name="update_client", methods={"PUT"})
     * @param Client $client
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function updateClient(Client $client, Request $request, SerializerInterface $serializer, ValidatorInterface $validator){
        $data = json_decode($request->getContent(), true);

        foreach ($data as $key => $value){
            if($key && !empty($value)) {
                $name = ucfirst($key);
                $setter = 'set'.$name;
                $client->$setter($value);
            }
        }

        $errors = $validator->validate($client);
        if(count($errors) > 0){
            $errorsSerialized = $serializer->serialize($errors, "json");
            return new Response($errorsSerialized, 400, ["Content-type" => "application/json"]);
        }

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($client);
        $manager->flush();

        $response = new Response(
            $serializer->serialize($client, "json", ["groups" => "client"]),
            200,
            ['Content-Type' => 'application/json']);

        return $response;
    }

    /**
     * @Route("/client/delete/{id}", name="delete_client", methods={"DELETE"})
     * @param Client $client
     * @return Response
     */
    public function deleteClient(Client $client){
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($client);
        $manager->flush();

        $response = new Response("", 204, ["Content-Type" => "application/json"]);

        return $response;
    }

    /*TODO VALIDATION (DONE)*/
    /*TODO OAUTH*/
    /*TODO PAGINATION (DONE)*/
    /*TODO LISTENER POUR LES ERREURS*/
    /*TODO CACHE*/
    /*TODO DOC*/

}
