<?php

namespace App\Controller;

use App\Form\FileUploadFormType;
use App\Form\TableTypeFormType;
use App\Service\TableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TableController extends AbstractController
{
    #[Route('/', name: 'list_tables')]
    public function listTables(Request $request,TableService $tableService): Response
    {
        $tables = $tableService->listTables();
        $message = $request->query->get('message');

        return $this->render('table/list_tables.html.twig', [
            'tables' => $tables,
            'message' => $message,
        ]);
    }

    #[Route('/create-table', name: 'create_table')]
    public function createTable(Request $request, TableService $tableService): Response
    {
        $form = $this->createForm(TableTypeFormType::class);
        $form->handleRequest($request);

        $message=null;

        if ($form->isSubmitted() && $form->isValid()) {
            $tableName = $form->get('name')->getData();

            try {
                $tableService->createTable($tableName);
                $message = 'Table created successfully!';

                return $this->redirectToRoute('list_tables', ['message' => $message]);
            } catch (\Exception $e) {
                $message = 'Error creating table: ' . $e->getMessage();
            }

        }

        return $this->render('table/create_table.html.twig', [
            'form' => $form->createView(),
            'message' => $message,
        ]);
    }

    #[Route('/import_data/{table_name}', name: 'import_data')]
    public function importData(Request $request, TableService $tableService, string $table_name): Response
    {
        set_time_limit(0);
        ini_set('memory_limit','3G');
        $form = $this->createForm(FileUploadFormType::class);
        $form->handleRequest($request);

        $message = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();

            try {
                $tableService->importDataFromJsonOrCsv($table_name, $file);
                $message = 'Data imported successfully!';
            } catch (\Exception $e) {
                $message = 'Error importing data: ' . $e->getMessage();
            }
        }

        return $this->render('table/import_data.html.twig', [
            'form' => $form->createView(),
            'message' => $message,
        ]);
    }

    #[Route('/anl_list/{table_name}', name: 'anl_list')]
    public function anlList(TableService $tableService, string $table_name): Response
    {
        $data = $tableService->getAnlList($table_name);

        return $this->render('table/anl_list.html.twig', [
            'table_name' => $table_name,
            'data' => $data,
        ]);
    }



    #[Route('/current_all/{tableName}', name: 'current_all')]
    public function currentAll(string $tableName, TableService $tableService):Response
    {
        $tableService->createTableCurrentOrVoltage($tableName, 'Current');
        return $this->redirectToRoute('list_tables');
    }

    #[Route('/voltage_all/{tableName}', name: 'voltage_all')]
    public function voltageAll(string $tableName, TableService $tableService):Response
    {
        $tableService->createTableCurrentOrVoltage($tableName, 'Voltage');
        return $this->redirectToRoute('list_tables');
    }
}
