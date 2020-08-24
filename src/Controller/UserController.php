<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Service\PaginationService;
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
     * @param SerializerInterface $serializer
     * @param $clientName
     * @param PaginationService $paginationService
     * @return Response
     */
    /*TODO AJOUTER LE NOM DU CLIENT*/
    public function listUserByClient(SerializerInterface $serializer, $clientName, PaginationService $paginationService)
    {
        $manager = $this->getDoctrine()->getManager();
        $clientId = $manager->getRepository(Client::class)->findOneBy(['name' => $clientName])->getId();

        $data = $manager->getRepository(User::class)
            ->findBy(
                [
                    "client" => $clientId
                ]
            );
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

        $data = $serializer->serialize($data, 'json', SerializationContext::create()->setGroups(array("user")));

        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/user/add", name="add_user", methods={"POST"})
     * @SWG\Response(
     *     response=201,
     *     description="Ajoute un utilisateur",
     *     @SWG\Schema(
     *
     *     @SWG\Items(ref=@Model(type=User::class, groups={"user"})
     *          )
     *      )
     *)
     * @ParamConverter("product") TODO A CHECKER
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function addUser(Request $request, SerializerInterface $serializer, UserPasswordEncoderInterface $encoder, ValidatorInterface $validator){
        $data = $request->getContent();
        $user = $serializer->deserialize($data, User::class,'json', ['groups'=> 'user']);

        $dataArray = json_decode($data, true);
        $encodedPassword = $encoder->encodePassword($user ,$dataArray['password']);
        $user->setPassword($encodedPassword);

        $clientId = $dataArray['client'];
        $client = $this->getDoctrine()->getManager()->getRepository(Client::class)->findOneBy(['id' => $clientId]);
        $user->setClient($client);
        $user->setRole("ROLE_USER");

        $manager = $this->getDoctrine()->getManager();

        $errors = $validator->validate($user);
        if(count($errors) > 0){
            $errorsSerialized = $serializer->serialize($errors, "json");
            /*TODO RENVOIS CLIENT SHOULD NOT BE BLANK S'IL NE TROUVE PAS LE CLIENT */
            return new Response($errorsSerialized, 400, ["Content-type" => "application/json"]);
        }

        $manager->persist($user);
        $manager->flush();

        $response = new Response($user);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/user/delete/{id}", name="delete_user", methods={"DELETE"})
     * @SWG\Response(
     *     response=204,
     *     description="Supprime un utilisateur",
     *     @SWG\Schema(
     *
     *     @SWG\Items(ref=@Model(type=User::class, groups={"user"})
     *          )
     *      )
     *)
     * @param User $user
     * @return Response
     */
    public function deleteUser(User $user){
        $manager = $this->getDoctrine()->getManager();
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
     * @param User $user
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function updateUser(User $user, Request $request, ValidatorInterface $validator, SerializerInterface $serializer){
        $manager = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(), true);

        if(isset($data['client'])){
            $data['client'] = $this->getDoctrine()->getManager()->getRepository(Client::class)->find($data['client']);
        }

        foreach ($data as $key => $value){
            if($key && !empty($value)) {
                $name = ucfirst($key);
                $setter = 'set'.$name;
                $user->$setter($value);
            }
        }

        $errors = $validator->validate($user);
        if(count($errors) > 0){
            $errorsSerialized = $serializer->serialize($errors, "json");
            return new Response($errorsSerialized, 400, ["Content-type" => "application/json"]);
        }

        $manager->persist($user);
        $manager->flush();

        return new Response($user);
    }

}
