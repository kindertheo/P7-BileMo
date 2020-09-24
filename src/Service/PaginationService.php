<?php

namespace App\Service;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaginationService extends  AbstractController {

    public function search($term, $order = 'asc', $limit = 20, $offset = 0)
    {
        $qb = $this
            ->createQueryBuilder('a')
            ->select('a')
            ->orderBy('a.title', $order);

        if ($term) {
            $qb
                ->where('a.title LIKE ?1')
                ->setParameter(1, '%'.$term.'%');
        }

        return $this->paginate($qb, $limit, $offset);
    }

    public function paginate(QueryBuilder $qb, $limit = 20, $offset = 0){
        if (0 == $limit || 0 >= $offset) {
            throw new \LogicException('$limit & $offset must be greater than 0.');
        }

        $pager = new Pagerfanta(new QueryAdapter($qb));
        $currentPage = ceil(($offset + 1) / $limit);
        $pager->setCurrentPage($currentPage);
        $pager->setMaxPerPage((int) $limit);

        return $pager;
    }

    public function paginateArray(array $array ){

        $request = Request::createFromGlobals();

        $limit = $request->query->get("limit");
        if(empty($limit)){
            $limit = 20;
        }
        $currentPage = $request->query->get("page");
        if(empty($currentPage) OR $currentPage <= 0){
            $currentPage = 1;
        }

        $adapter = new ArrayAdapter($array);
        $pager = new Pagerfanta($adapter);

        $pager->setMaxPerPage((int) $limit);
        try {
            $pager->setCurrentPage($currentPage);
        }
        catch(NotValidCurrentPageException $e){
            throw new NotFoundHttpException("Page Not Found");
        }

        $return = ["Page" => $currentPage,
            "Limite" => $limit,
            "Resultat" => $pager->getCurrentPageResults()];
        
        return $return;
    }
}