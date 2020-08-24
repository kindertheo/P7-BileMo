<?php

namespace App\Service;

use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CheckingErrorsService {

    private $validator;

    private $serializer;

    public function __construct(ValidatorInterface $validator, SerializerInterface $serializer )
    {
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    public function errorsValidation($product){

        $errors = $this->validator->validate($product);
        if(count($errors) > 0){

            $errorsSerialized = $this->serializer->serialize($errors, "json");
            throw new \LogicException($errorsSerialized, 400);
        }

    }
}