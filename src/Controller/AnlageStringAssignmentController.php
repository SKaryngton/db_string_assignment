<?php

namespace App\Controller;

use App\Entity\Anlage;
use App\Entity\AnlageStringAssignment;
use App\Form\AnlageStringAssignment2Type;
use App\Form\AnlageStringAssignmentType;
use App\Service\ExcelImporter;
use App\Service\G4NService_2;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AnlageStringAssignmentController extends AbstractController
{
    #[Route('/upload', name: 'app_anlage_string_assignment')]
    public function upload(Request $request, ExcelImporter $excelImporter): Response
    {
        $form = $this->createForm(AnlageStringAssignmentType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['file']->getData();
            $database = $form['database']->getData();
            $anlage = $form['anlage']->getData();

            if ($file) {
                $success = $excelImporter->import($file->getRealPath(), $database,$anlage);

                if ($success) {
                    $this->addFlash('success', 'Success');
                } else {
                    $this->addFlash('error', 'Error');
                }
            }
        }

        return $this->render('anlage_string_assignment/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/', name: 'app_anlage_string_assignment_upload')]
    public function upload2(Request $request, EntityManagerInterface $entityManager): Response
    {

        $assignments = $entityManager->getRepository(AnlageStringAssignment::class)->findAll();


        $anlageWithAssignments = [];
        foreach ($assignments as $assignment) {
            $anlageWithAssignments[$assignment->getAnlId()->getAnlId()] = true;
        }

        $form = $this->createForm(AnlageStringAssignment2Type::class,null, [
            'anlageWithAssignments' => $anlageWithAssignments,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['file']->getData();
            $anlage = $form['anlage']->getData();


            if ($anlage) {
                $existingAssignments = $entityManager->getRepository(AnlageStringAssignment::class)->findBy(['anlId' => $anlage]);
                foreach ($existingAssignments as $assignment) {
                    $entityManager->remove($assignment);
                }
                $entityManager->flush();

                $anlage->setLastUploadDate(new \DateTime());
                $entityManager->persist($anlage);
                $entityManager->flush();
            }

            if ($file) {
                $xlsx = SimpleXLSX::parse($file->getRealPath());
                if ($xlsx) {
                    $firstRow = true;
                    foreach ($xlsx->rows() as $row) {

                        if ($firstRow) {
                            $firstRow = false;
                            continue;
                        }
                        $assignment = new AnlageStringAssignment();
                        $assignment->setStationNr($row[0]);
                        $assignment->setInverterNr($row[1]);
                        $assignment->setStringNr($row[2]);
                        $assignment->setChannelNr($row[3]);
                        $assignment->setStringActive($row[4]);
                        $assignment->setChannelCat($row[5]);
                        $assignment->setPosition($row[6]);
                        $assignment->setTilt($row[7]);
                        $assignment->setAzimut($row[8]);
                        $assignment->setPanelType($row[9]);
                        $assignment->setInverterType($row[10]);
                        $assignment->setAnlId($anlage);
                        $entityManager->persist($assignment);
                    }
                    $entityManager->flush();

                    $this->addFlash('success', 'Success');
                    return $this->redirectToRoute('app_anlage_string_assignment_upload');

                }

                $this->addFlash('error', 'Error');
            }
        }




        return $this->render('anlage_string_assignment/upload.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/export', name: 'export_anlage_string_assignments')]
    public function exportAnlageStringAssignments(EntityManagerInterface $entityManager): Response
    {
        // Récupération des données
        $assignments = $entityManager->getRepository(AnlageStringAssignment::class)->findAll();

        // Préparation des données pour l'export
        $data = [];
        $header = ['Station Nr', 'Inverter Nr', 'String Nr', 'Channel Nr', 'String Active', 'Channel Cat', 'Position', 'Tilt', 'Azimut', 'Panel Type', 'Inverter Type','AnlId'];
        $data[] = $header;

        foreach ($assignments as $assignment) {


            $data[] = [
                $assignment->getStationNr(),
                $assignment->getInverterNr(),
                $assignment->getStringNr(),
                $assignment->getChannelNr(),
                $assignment->getStringActive(),
                $assignment->getChannelCat(),
                $assignment->getPosition(),
                $assignment->getTilt(),
                $assignment->getAzimut(),
                $assignment->getPanelType(),
                $assignment->getInverterType(),
                ($entityManager->getRepository(Anlage::class)->find( $assignment->getAnlId()->getId()))->getAnlId()

            ];
        }

        // Génération du fichier Excel
        $xlsx = SimpleXLSXGen::fromArray($data);
        $filename = 'AnlageStringAssignments_'.date('Y-m-d').'.xlsx';

        // Envoi du fichier à l'utilisateur
        return new Response($xlsx->downloadAs($filename), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    #[Route('/test', name: 'export_anlage_string_assignments_test')]
    public function test(G4NService_2 $service): Response
    {

        $month = 7;
        $year = 2024; // Define the year
        $suffix ='CX104';

        // Using DateTime to set start and end dates
        $dateX = new DateTime("$year-$month-01 00:00:00");
        $dateY = (clone $dateX)->modify('last day of this month')->setTime(23, 59, 59);

        // Transfer data for the specified date range
       // $service->transferData($suffix,$dateX->format('Y-m-d H:i:s'), $dateY->format('Y-m-d H:i:s'));

        return $this->render('test.html.twig');

    }
}
