<?php

namespace App\Controller;

use App\Entity\Client;
use App\Service\CheckingErrorsService;
use App\Service\JsonToEntityService;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface;
use Swagger\Annotations as SWG;

class ClientController extends AbstractController
{
    /**
     * @Route("/client/add", name="add_client", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     * @SWG\Response(
     *     response=201,
     *     description="Ajoute un client",
     *     @SWG\Schema(
     *
     *     @SWG\Items(ref=@Model(type=Client::class, groups={"client"})
     *          )
     *      )
     *)
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Champs client à compléter",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(
     *            type="object",
     *            @SWG\Property(property="name", type="string"),
     *         )
     *     )
     * )
     * @SWG\Parameter(
     *     name="role",
     *     in="header",
     *     description="Requiert le rôle d'administrateur",
     *     type="string"
     * )
     * @SWG\Tag(name="Administrateur")
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param CheckingErrorsService $errorsService
     * @return Response
     */
    public function addClient(Request $request, SerializerInterface $serializer, CheckingErrorsService $errorsService){

        $client = $serializer->deserialize($request->getContent(), Client::class, "json", DeserializationContext::create()->setGroups("client"));

        $manager = $this->getDoctrine()->getManager();

        $errorsService->errorsValidation($client);

        $manager->persist($client);
        $manager->flush();

        $jsonResponse = new JsonResponse(
            $serializer->serialize($client, "json", SerializationContext::create()->setGroups(array("client"))),
            JsonResponse::HTTP_CREATED,
            [],
            true
        );

        return $jsonResponse;
    }

    /**
     * @Route("/client/{id}", name="show_client", methods={"GET"})
     * @IsGranted("ROLE_CLIENT")
     * @SWG\Response(
     *     response=200,
     *     description="Affiche un client",
     *     @SWG\Schema(
     *
     *     @SWG\Items(ref=@Model(type=Client::class, groups={"client"})
     *          )
     *      )
     *)
     * @SWG\Parameter(
     *     name="role",
     *     in="header",
     *     description="Requiert d'être un utilisateur lié au client",
     *     type="string"
     * )
     * @SWG\Tag(name="Utilisateur")
     * @param Client $client
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function showClient(Client $client, SerializerInterface $serializer){
        $this->denyAccessUnlessGranted("view", $client);

        $clientJson = $serializer->serialize($client, "json", SerializationContext::create()->setGroups(array("client")));
        $response = new Response($clientJson, 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/client/update/{id}", name="update_client", methods={"PUT"})
     * @IsGranted("ROLE_ADMIN")
     * @SWG\Response(
     *     response=200,
     *     description="Modifie un client",
     *     @SWG\Schema(
     *
     *     @SWG\Items(ref=@Model(type=Client::class, groups={"client"})
     *          )
     *      )
     *)
     * @SWG\Tag(name="Administrateur")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Champs client à compléter",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(
     *            type="object",
     *            @SWG\Property(property="name", type="string"),
     *         )
     *     )
     * )
     * @SWG\Parameter(
     *     name="role",
     *     in="header",
     *     description="Requiert le rôle d'administrateur",
     *     type="string"
     * )
     * @param Client $client
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param CheckingErrorsService $errorsService
     * @param JsonToEntityService $jsonToEntityService
     * @return Response
     */
    public function updateClient(Client $client, Request $request, SerializerInterface $serializer, CheckingErrorsService $errorsService, JsonToEntityService $jsonToEntityService){
        $data = json_decode($request->getContent(), true);

        $client = $jsonToEntityService->JsonToEntity($client, $data);

        $errorsService->errorsValidation($client);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($client);
        $manager->flush();

        $response = new Response(
            $serializer->serialize($client, "json", SerializationContext::create()->setGroups(array("client"))),
            200,
            ['Content-Type' => 'application/json']);

        return $response;
    }

    /**
     * @Route("/client/delete/{id}", name="delete_client", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     * @SWG\Response(
     *     response=204,
     *     description="Supprime un client",
     *     @SWG\Schema(
     *
     *     @SWG\Items(ref=@Model(type=Client::class, groups={"client"})
     *          )
     *      )
     *)
     * @SWG\Parameter(
     *     name="role",
     *     in="header",
     *     description="Requiert le rôle d'administrateur",
     *     type="string"
     * )     * @SWG\Tag(name="Administrateur")
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

    /*TODO CACHE*/
    /*TODO DIAGRAM UML*/

}
