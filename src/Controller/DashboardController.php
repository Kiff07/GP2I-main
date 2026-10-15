<?php

namespace App\Controller;

use App\Entity\Materials;
use App\Repository\CategoriesRepository;
use App\Repository\LoansRepository;
use App\Repository\DeliveriesRepository;
use App\Repository\InventoryMovementsRepository;
use App\Repository\MaterialsRepository;
use App\Repository\PurchaseOrdersRepository;
use App\Repository\SuppliersRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(
        MaterialsRepository $materialsRepository,
        CategoriesRepository $categoriesRepository,
        SuppliersRepository $suppliersRepository,
        PurchaseOrdersRepository $purchaseOrdersRepository,
        DeliveriesRepository $deliveriesRepository,
        InventoryMovementsRepository $inventoryMovementsRepository,
        UsersRepository $usersRepository,
        LoansRepository $loansRepository,
    ): Response {
        return $this->render('dashboard/index.html.twig', [
            'materials' => $materialsRepository->findBy([], ['id' => 'DESC'], 5),
            'materials_count' => $materialsRepository->count([]),
            'categories_count' => $categoriesRepository->count([]),
            'suppliers_count' => $suppliersRepository->count([]),
            'orders_count' => $purchaseOrdersRepository->count([]),
            'deliveries_count' => $deliveriesRepository->count([]),
            'movements_count' => $inventoryMovementsRepository->count([]),
            'users_count' => $usersRepository->count([]),
            'status_counts' => $materialsRepository->countByStatus(),
            'movement_stats' => $inventoryMovementsRepository->countByMonth(6),
            'overdue_loans' => $loansRepository->findOverdue(),
            'active_loans_count' => $loansRepository->countActive(),
            'status_choices' => Materials::statusChoices(),
            'low_stock' => $materialsRepository->findLowStock(),
        ]);
    }
}
