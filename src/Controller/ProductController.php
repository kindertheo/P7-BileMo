<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

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
     * @return Response
     */
    public function showList(SerializerInterface $serializer){
        $data = $this->getDoctrine()->getManager()->getRepository(Product::class)->findAll();

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
     * @return Response
     */
    public function addProduct(Request $request, SerializerInterface $serializer){
        $data = $request->getContent();
        $product = $serializer->deserialize($data, Product::class, 'json');

        $manager = $this->getDoctrine()->getManager();

        $manager->persist($product);
        $manager->flush();

        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
