<?php

namespace App\DataFixtures;

use App\Entity\Categories;
use App\Entity\Materials;
use App\Entity\Suppliers;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // 1. Création de quelques Catégories
        $categories = [];
        $categoryNames = ['Informatique', 'Bureautique', 'Audiovisuel', 'Mobilier', 'Outillage'];
        
        foreach ($categoryNames as $name) {
            $category = new Categories();
            if (method_exists($category, 'setName')) {
                $category->setName($name);
            }
            if (method_exists($category, 'setDescription')) {
                $category->setDescription($faker->sentence());
            }
            $manager->persist($category);
            $categories[] = $category;
        }

        // 2. Création de quelques Fournisseurs
        $suppliers = [];
        $supplierNames = ['Dell Technologies', 'HP France', 'LDLC Pro', 'Amazon Business', 'Manutan'];
        
        foreach ($supplierNames as $name) {
            $supplier = new Suppliers();
            if (method_exists($supplier, 'setName')) {
                $supplier->setName($name);
            }
            if (method_exists($supplier, 'setEmail')) {
                $supplier->setEmail($faker->companyEmail());
            }
            if (method_exists($supplier, 'setPhone')) {
                $supplier->setPhone($faker->phoneNumber());
            }
            $manager->persist($supplier);
            $suppliers[] = $supplier;
        }

        // 3. Liste des statuts possibles
        $statuses = [
            Materials::STATUS_AVAILABLE,
            Materials::STATUS_BORROWED,
            Materials::STATUS_MAINTENANCE,
            Materials::STATUS_BROKEN,
            Materials::STATUS_DISPOSED,
        ];

        // 4. Exemples de noms de matériel
        $materialExamples = [
            'Ordinateur portable Latitude', 'Écran 27 pouces 4K', 'Souris sans fil Ergonomique',
            'Clavier mécanique', 'Vidéoprojecteur HD', 'Tablette graphique',
            'Imprimante Laser', 'Microphone USB', 'Casque antibruit', 'Disque dur externe 2To'
        ];

        // 5. Création de 20 matériels
        for ($i = 1; $i <= 20; $i++) {
            $material = new Materials();
            $material->setName($faker->randomElement($materialExamples) . ' ' . $faker->randomNumber(3, true));
            $material->setReference('MAT-' . strtoupper($faker->bothify('##??##')));
            $material->setSerialNumber($faker->uuid());
            $material->setQuantity($faker->numberBetween(1, 15));
            $material->setStatus($faker->randomElement($statuses));
            $material->setLocation('Bureau ' . $faker->randomElement(['A101', 'B204', 'C302', 'Salle Serveur', 'Stockage']));
            $material->setPurchaseDate($faker->dateTimeBetween('-2 years', 'now'));
            $material->setPurchasePrice((string) $faker->randomFloat(2, 50, 1500));
            
            // Liaison avec une catégorie et un fournisseur aléatoires
            $material->setCategory($faker->randomElement($categories));
            $material->setSupplier($faker->randomElement($suppliers));

            $manager->persist($material);
        }

        // Envoi en base de données
        $manager->flush();
    }
}