<?php

namespace App\Command;

use App\Entity\Archives;
use App\Entity\AssetImages;
use App\Entity\Categories;
use App\Entity\Deliveries;
use App\Entity\InventoryMovements;
use App\Entity\Materials;
use App\Entity\PurchaseItems;
use App\Entity\PurchaseOrders;
use App\Entity\Suppliers;
use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:load-demo-data', description: 'Charge des donnees de demonstration GP2I.')]
class LoadDemoDataCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UsersRepository $usersRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->usersRepository->findOneBy(['email' => 'admin@gp2i.local'])) {
            $output->writeln('Les donnees de demonstration existent deja.');

            return Command::SUCCESS;
        }

        $admin = (new Users())
            ->setUsername('admin')
            ->setEmail('admin@gp2i.local')
            ->setFullName('Administrateur GP2I')
            ->setPhone('0102030405')
            ->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));

        $manager = (new Users())
            ->setUsername('gestionnaire')
            ->setEmail('gestionnaire@gp2i.local')
            ->setFullName('Gestionnaire Stock')
            ->setRoles(['ROLE_MANAGER']);
        $manager->setPassword($this->passwordHasher->hashPassword($manager, 'demo123'));

        $computers = (new Categories())->setName('Informatique')->setDescription('Ordinateurs, ecrans et accessoires.');
        $network = (new Categories())->setName('Reseau')->setDescription('Equipements reseau et connectique.');

        $supplier = (new Suppliers())
            ->setName('Tech Pro Distribution')
            ->setContactName('Nadia Martin')
            ->setEmail('contact@techpro.local')
            ->setPhone('0499001122')
            ->setAddress('15 rue des Solutions, 75000 Paris');

        $laptop = (new Materials())
            ->setName('Laptop Dell Latitude')
            ->setReference('MAT-PC-001')
            ->setSerialNumber('DL-2026-001')
            ->setCategory($computers)
            ->setQuantity(8)
            ->setStatus(Materials::STATUS_AVAILABLE)
            ->setLocation('Stock principal')
            ->setPurchaseDate(new \DateTime('-30 days'))
            ->setPurchasePrice('899.90');

        $switch = (new Materials())
            ->setName('Switch Cisco 24 ports')
            ->setReference('MAT-RES-001')
            ->setSerialNumber('CS-24P-7788')
            ->setCategory($network)
            ->setQuantity(2)
            ->setStatus(Materials::STATUS_MAINTENANCE)
            ->setLocation('Salle serveur')
            ->setPurchaseDate(new \DateTime('-60 days'))
            ->setPurchasePrice('1290.00');

        $order = (new PurchaseOrders())
            ->setSupplier($supplier)
            ->setOrderNumber('PO-2026-0001')
            ->setOrderedBy($admin)
            ->setStatus(PurchaseOrders::STATUS_RECEIVED)
            ->setOrderDate(new \DateTime('-35 days'))
            ->setExpectedDate(new \DateTime('-20 days'))
            ->setTotalAmount('3090.00');

        $order->addItem((new PurchaseItems())
            ->setMaterialName('Laptop Dell Latitude')
            ->setMaterialReference('MAT-PC-001')
            ->setQuantity(3)
            ->setUnitPrice('899.90')
            ->setReceivedQuantity(3));
        $order->addItem((new PurchaseItems())
            ->setMaterialName('Switch Cisco 24 ports')
            ->setMaterialReference('MAT-RES-001')
            ->setQuantity(1)
            ->setUnitPrice('1290.00')
            ->setReceivedQuantity(1));

        $delivery = (new Deliveries())
            ->setPurchaseOrder($order)
            ->setDeliveryDate(new \DateTime('-18 days'))
            ->setReceivedBy($manager)
            ->setNote('Livraison complete, materiel controle.');

        $movement = (new InventoryMovements())
            ->setMaterial($laptop)
            ->setMovementType(InventoryMovements::TYPE_IN)
            ->setQuantity(3)
            ->setToLocation('Stock principal')
            ->setPerformedBy($manager)
            ->setPerformedAt(new \DateTime('-18 days'))
            ->setReason('Reception commande PO-2026-0001');

        $image = (new AssetImages())
            ->setMaterial($laptop)
            ->setFilename('demo-laptop.jpg')
            ->setCaption('Photo de demonstration');

        $archive = (new Archives())
            ->setEntity('materials')
            ->setEntityId(1)
            ->setAction('demo_seed')
            ->setData(['source' => 'app:load-demo-data'])
            ->setCreatedBy($admin);

        foreach ([$admin, $manager, $computers, $network, $supplier, $laptop, $switch, $order, $delivery, $movement, $image, $archive] as $entity) {
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
        $output->writeln('Donnees de demonstration chargees.');

        return Command::SUCCESS;
    }
}
