<?php

namespace App\Service;

class JsonToEntityService {

    public function JsonToEntity($entity, $data){

        foreach ($data as $key => $value){
            if($key && !empty($value)) {
                $name = ucfirst($key);
                $setter = 'set'.$name;
                $entity->$setter($value);
            }
        }

        return $entity;
    }
}