<?php

namespace App\Controller;

use App\Service\G4NService;
use App\Service\G4NService_2;
use App\Service\Pvp_Service;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ServiceController extends AbstractController
{
    #[Route('/service', name: 'app_service')]
    public function index(Request $request, G4NService $service): JsonResponse
    {



        $x = $request->query->get('x');
        $y = $request->query->get('y');


        if (null === $x || null === $y) {
            return $this->json(['error' => 'Missing parameters x or y.Example ?x=2021-10-01 &y=2023-09-01'], Response::HTTP_BAD_REQUEST);
        }


        try {


            $service->processTables($x,$y);


            return $this->json(['success' => 'Data processed successfully']);

        } catch (\Exception $e) {

            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/copy', name: 'app_copy')]
    public function copy(Request $request, Pvp_Service $service): JsonResponse
    {

        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $t=$request->query->get('t');
        $x = $request->query->get('x');
        $y = $request->query->get('y');


        if (null === $x || null === $y || null === $t) {
            return $this->json(['error' => 'Missing parameters x or y.Example ?t=tablename&x=2021-10-01&y=2023-09-01 '], Response::HTTP_BAD_REQUEST);
        }


        try {


           $service->copy($t,$x,$y);


            return $this->json(['success' => 'Data copied successfully']);

        } catch (\Exception $e) {

            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/transfer', name: 'app_transfer')]
    public function transfer(G4NService_2 $service): JsonResponse
    {


            $month=4;
            $startDate = date('Y-m-01 00:00:00', strtotime("2023-$month-01"));
            $endDate = date('Y-m-t 23:59:59', strtotime("2023-$month-01"));


            try {

                $service->transferData($startDate,$endDate );
                return $this->json(['success' => 'Data processed successfully']);
            } catch (\Exception $e) {

                return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }



//        for ($month = 1; $month <= 6; $month++) {
//
//            $startDate = date('Y-m-01 00:00:00', strtotime("2023-$month-01"));
//            $endDate = date('Y-m-t 23:59:59', strtotime("2023-$month-01"));
//
//
//            try {
//
//                $service->transferData($startDate,$endDate );
//
//            } catch (\Exception $e) {
//
//                return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
//            }
//        }
//        return $this->json(['success' => 'Data processed successfully']);
    }
}
