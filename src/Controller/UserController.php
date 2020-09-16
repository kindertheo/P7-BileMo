<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Service\CheckingErrorsService;
use App\Service\JsonToEntityService;
use App\Service\PaginationService;
use JMS\Serializer\DeserializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use JMS\Serializer\SerializationContext;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;


class UserController extends AbstractController
{

    /**
     * @Route("/user/{clientName}", name="list_user", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Retourne tout les utilisateurs selon un client",
     *     @SWG\Schema(
     *
     *     @SWG\Items(ref=@Model(type=User::class, groups={"user"})
     *          )
     *      )
     *)
     * @SWG\Tag(name="Utilisateur")
     * @SWG\Parameter(
     *     name="role",
     *     in="header",
     *     description="Seul les utilisateurs du même client peuvent être consulter",
     *     type="string"
     * )
     * @param SerializerInterface $serializer
     * @param $clientName
     * @param PaginationService $paginationService
     * @return Response
     */
    public function listUserByClient(SerializerInterface $serializer, $clientName, PaginationService $paginationService)
    {
        $manager = $this->getDoctrine()->getManager();
        $client = $manager->getRepository(Client::class)->findOneBy(['name' => $clientName]);
        if(!$client){
            throw new \LogicException("Client not found!", 404);
        }
        $clientId = $client->getId();


        $data = $manager->getRepository(User::class)
            ->findBy(
                [
                    "client" => $clientId
                ]
            );

        $this->denyAccessUnlessGranted("view", $manager->getRepository(Client::class)->find($clientId));

        $data = $paginationService->paginateArray($data);

        $data = $serializer->serialize($data, "json", SerializationContext::create()->setGroups(array("user")));

        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }


    /**
     * @Route("/user/{clientName}/{userId}", name="show_user", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Retourne un utilisateur selon un client",
     *     @SWG\Schema(
     *
     *     @SWG\Items(ref=@Model(type=User::class, groups={"user"})
     *          )
     *      )
     *)
     * @SWG\Parameter(
     *     name="role",
     *     in="header",
     *     description="Seul les utilisateurs du même client peuvent être consulter",
     *     type="string"
     * )
     * @SWG\Tag(name="Utilisateur")
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

        /*Verify if user is part of client*/
        $this->denyAccessUnlessGranted("view", $manager->getRepository(Client::class)->find($clientId));

        $data = $serializer->serialize($data, 'json', SerializationContext::create()->setGroups(array("user")));

        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/user/add", name="add_user", methods={"POST"})
     * @IsGranted("ROLE_CLIENT")
     * @SWG\Response(
     *     response=201,
     *     description="Ajoute un utilisateur",
     *     @SWG\Schema(
     *              @SWG\Items(ref=@Model(type=User::class, groups={"user"})
     *          )
     *      )
     *)
     * @SWG\Tag(name="Client")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Champs utilisateur a compléter",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(
     *            type="object",
     *            @SWG\Property(property="name", type="string"),
     *            @SWG\Property(property="email", type="string"),
     *            @SWG\Property(property="client", type="integer"),
     *            @SWG\Property(property="password", type="string")
     *         )
     *     )
     * )
     * @SWG\Parameter(
     *     name="role",
     *     in="header",
     *     description="Requiert le rôle de client ou d'administrateur",
     *     type="string"
     * )
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param UserPasswordEncoderInterface $encoder
     * @param CheckingErrorsService $errorsService
     * @return Response
     */
    public function addUser(Request $request, SerializerInterface $serializer, UserPasswordEncoderInterface $encoder, CheckingErrorsService $errorsService){
        $data = $request->getContent();
        $user = $serializer->deserialize($data, User::class,'json', DeserializationContext::create()->setGroups(['groups'=> 'user']) );

        $dataArray = json_decode($data, true);
        $encodedPassword = $encoder->encodePassword($user ,$dataArray['password']);
        $user->setPassword($encodedPassword);

        $clientId = $this->getUser()->getClient();
        $user->setClient($clientId);
        $user->setRole("ROLE_USER");

        $manager = $this->getDoctrine()->getManager();

        $errorsService->errorsValidation($user);

        $manager->persist($user);
        $manager->flush();

        $response = new Response($serializer->serialize($user, "json"));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/user/delete/{id}", name="delete_user", methods={"DELETE"})
     * @IsGranted("ROLE_CLIENT")
     * @SWG\Response(
     *     response=204,
     *     description="Supprime un utilisateur",
     *     @SWG\Schema(
     *
     *     @SWG\Items(ref=@Model(type=User::class, groups={"user"})
     *          )
     *      )
     *)
     * @SWG\Parameter(
     *     name="role",
     *     in="header",
     *     description="Requiert le rôle de client ou d'administrateur",
     *     type="string"
     * )
     * @SWG\Tag(name="Client")
     * @param User $user
     * @return Response
     */
    public function deleteUser(User $user){

        $manager = $this->getDoctrine()->getManager();

        $this->denyAccessUnlessGranted("view", $user->getClient());

        $manager->remove($user);
        $manager->flush();

        return new Response("", 204);
    }

    /**
     * @Route("/user/update/{id}", name="update_user", methods={"PUT"})
     * @SWG\Response(
     *     response=200,
     *     description="Modifie un utilisateur",
     *     @SWG\Schema(
     *
     *     @SWG\Items(ref=@Model(type=User::class, groups={"user"})
     *          )
     *      )
     *)
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Champs utilisateur a compléter",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(
     *            type="object",
     *            @SWG\Property(property="name", type="string"),
     *            @SWG\Property(property="email", type="string"),
     *            @SWG\Property(property="client", type="integer"),
     *            @SWG\Property(property="password", type="string")
     *         )
     *     )
     * )
     * @SWG\Tag(name="Client")
     * @SWG\Parameter(
     *     name="role",
     *     in="header",
     *     description="Ne peut s'utiliser que sur soi même ou requiert le rôle de client ou d'administrateur",
     *     type="string"
     * )
     * @param User $user
     * @param Request $request
     * @param JsonToEntityService $jsonToEntityService
     * @param CheckingErrorsService $errorsService
     * @return Response
     */
    public function updateUser(User $user, Request $request, JsonToEntityService $jsonToEntityService, CheckingErrorsService $errorsService, SerializerInterface $serializer){
        $manager = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(), true);


        if( isset($data['client']) ){
            $client = $this->getDoctrine()->getManager()->getRepository(Client::class)->find($data['client']);
            $data['client'] = $client;
        }else{
            $client = $this->getUser()->getClient();
            $data['client'] = $client;
        }

        $this->denyAccessUnlessGranted("view", $client);

        $user = $jsonToEntityService->JsonToEntity($user, $data);
        $errorsService->errorsValidation($user);

        $manager->persist($user);
        $manager->flush();

        return new Response($user);
    }

}
