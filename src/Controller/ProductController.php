<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\CheckingErrorsService;
use App\Service\JsonToEntityService;
use App\Service\PaginationService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\Serializer\SerializerInterface;
use Swagger\Annotations as SWG;


class ProductController extends AbstractController
{

    /**
     * @Route("/product/{id}", name="show_product", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Retourne un produit",
     *     @SWG\Schema(
     *
     *     @SWG\Items(ref=@Model(type=Product::class)
     *          )
     *      )
     *)
     * @SWG\Tag(name="Administrateur")
     * @SWG\Parameter(
     *     name="role",
     *     in="header",
     *     description="Toutes les personnes authentifiées peuvent consulter le catalogue produit",
     *     type="string"
     * )
     * @ParamConverter()
     * @param Product $product
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function showProduct(Product $product, SerializerInterface $serializer){

        $data = $serializer->serialize($product, 'json');

        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/product", name="list_product", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Retourne la liste de tout les produits",
     *     @SWG\Schema(
     *
     *     @SWG\Items(ref=@Model(type=Product::class)
     *          )
     *      )
     *)
     * @SWG\Parameter(
     *     name="role",
     *     in="header",
     *     description="Toute les personnes authentifiées peuvent consulter le catalogue produits",
     *     type="string"
     * )
     * @SWG\Tag(name="Utilisateur")
     * @param SerializerInterface $serializer
     * @param PaginationService $paginationService
     * @return Response
     */
    public function showList(SerializerInterface $serializer, PaginationService $paginationService){
        $data = $this->getDoctrine()->getManager()->getRepository(Product::class)->findAll();

        $data = $paginationService->paginateArray($data);

        $data = $serializer->serialize($data, "json");

        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/product/add", name="add_product", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     * @SWG\Response(
     *     response=201,
     *     description="Ajoute un produit",
     *     @SWG\Schema(
     *
     *     @SWG\Items(ref=@Model(type=Product::class)
     *          )
     *      )
     *)
     * @SWG\Tag(name="Administrateur")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Champs produit à compléter",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(
     *            type="object",
     *            @SWG\Property(property="name", type="string"),
     *            @SWG\Property(property="prince", type="integer"),
     *            @SWG\Property(property="description", type="integer"),
     *         )
     *     )
     * )
     * @SWG\Parameter(
     *     name="role",
     *     in="header",
     *     description="Requiert le rôle d'administrateur",
     *     type="string"
     * )
     * @ParamConverter("product")
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param CheckingErrorsService $errorsService
     * @return Response
     */
    public function addProduct(Request $request, SerializerInterface $serializer, CheckingErrorsService $errorsService){
        $data = $request->getContent();
        $product = $serializer->deserialize($data, Product::class, 'json');


        $errorsService->errorsValidation($product);

        $manager = $this->getDoctrine()->getManager();

        $manager->persist($product);
        $manager->flush();

        $response = new Response($data, 201);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/product/delete/{id}", name="delete_product", methods={"DELETE"} )
     * @IsGranted("ROLE_ADMIN")
     * @SWG\Response(
     *     response=204,
     *     description="Supprime un produit",
     *     @SWG\Schema(
     *
     *     @SWG\Items(ref=@Model(type=Product::class)
     *          )
     *      )
     *)
     * @SWG\Tag(name="Administrateur")
     * @SWG\Parameter(
     *     name="role",
     *     in="header",
     *     description="Requiert le rôle d'administrateur",
     *     type="string"
     * )
     * @param Product $product
     * @return Response
     */
    public function deleteProduct(Product $product){
        $manager = $this->getDoctrine()->getManager();

        $manager->remove($product);
        $manager->flush();

        $jsonResponse = new JsonResponse("", 204);
        return $jsonResponse;
    }

    /**
     * @Route("/product/update/{id}", name="update_product", methods={"PUT"} )
     * @IsGranted("ROLE_ADMIN")
     * @SWG\Response(
     *     response=200,
     *     description="Modifie un produit",
     *     @SWG\Schema(
     *
     *     @SWG\Items(ref=@Model(type=Product::class)
     *          )
     *      )
     *)
     * @SWG\Tag(name="Administrateur")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Champs produit à compléter",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(
     *            type="object",
     *            @SWG\Property(property="name", type="string"),
     *            @SWG\Property(property="prince", type="integer"),
     *            @SWG\Property(property="description", type="integer"),
     *         )
     *     )
     * )
     * @SWG\Parameter(
     *     name="role",
     *     in="header",
     *     description="Requiert le rôle d'administrateur",
     *     type="string"
     * )
     * @param Product $product
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param CheckingErrorsService $errorsService
     * @param JsonToEntityService $jsonToEntityService
     * @return Response
     */
    public function updateProduct(Product $product,Request $request, SerializerInterface $serializer, CheckingErrorsService $errorsService, JsonToEntityService $jsonToEntityService){
        $manager = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(), true);

        $product = $jsonToEntityService->JsonToEntity($product, $data);

        $errorsService->errorsValidation($product);


        $manager->persist($product);
        $manager->flush();

        $productJson = $serializer->serialize($product, "json");
        $response = new Response($productJson, 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

}
