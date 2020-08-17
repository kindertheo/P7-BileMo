<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductController extends AbstractController
{
    /**
     * @Route("/product/index", name="product")
     */
    public function index()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ProductController.php',
        ]);
    }

    /**
     * @Route("/product/{id}", name="show_product", methods={"GET"})
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
     * @ParamConverter("product")
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function addProduct(Request $request, SerializerInterface $serializer, ValidatorInterface $validator){
        $data = $request->getContent();
        $product = $serializer->deserialize($data, Product::class, 'json');

        $manager = $this->getDoctrine()->getManager();

        $errors = $validator->validate($product);
        if(count($errors) > 0){
            $errorsSerialized = $serializer->serialize($errors, "json");
            return new Response($errorsSerialized, 400, ["Content-type" => "application/json"]);
        }

        $manager->persist($product);
        $manager->flush();

        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/product/delete/{id}", name="delete_product", methods={"DELETE"} )
     * @param Product $product
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function deleteProduct(Product $product, SerializerInterface $serializer){
        $manager = $this->getDoctrine()->getManager();

        $manager->remove($product);
        $manager->flush();

        $jsonResponse = new JsonResponse("", 204);
        $response = new Response("", 204);
        return $jsonResponse;
    }

    /**
     * @Route("/product/update/{id}", name="update_product", methods={"PUT"} )
     * @param Product $product
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @return Response
     */
    /*TODO METTRE DANS UN SERVICE*/
    public function updateProduct(Product $product,Request $request, SerializerInterface $serializer, ValidatorInterface $validator){
        $manager = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(), true);

        foreach ($data as $key => $value){
            if($key && !empty($value)) {
                $name = ucfirst($key);
                $setter = 'set'.$name;
                $product->$setter($value);
            }
        }

        $errors = $validator->validate($product);
        if(count($errors) > 0){
            $errorsSerialized = $serializer->serialize($errors, "json");
            return new Response($errorsSerialized, 400, ["Content-type" => "application/json"]);
        }

        $manager->persist($product);
        $manager->flush();

        $productJson = $serializer->serialize($product, "json");
        $response = new Response($productJson, 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /*TODO ERREUR ENCODAGE, A VERIFIER*/
}
