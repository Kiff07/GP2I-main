<?php

namespace App\Command;

use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Cree (ou met a jour) un compte administrateur en ligne de commande.
 *
 * Exemple : php bin/console app:create-admin admin@gp2i.local admin
 * Indispensable depuis que tout le site exige une connexion : sans compte,
 * personne ne peut se connecter la premiere fois.
 */
#[AsCommand(name: 'app:create-admin', description: 'Cree ou met a jour un compte administrateur GP2I.')]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UsersRepository $usersRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de connexion')
            ->addArgument('username', InputArgument::REQUIRED, 'Identifiant affiche');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = trim((string) $input->getArgument('email'));
        $username = trim((string) $input->getArgument('username'));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('Email invalide : '.$email);

            return Command::FAILURE;
        }

        $helper = $this->getHelper('question');
        $password = (string) $helper->ask($input, $output, (new Question('Mot de passe (min. 8 caracteres) : '))->setHidden(true)->setHiddenFallback(false));
        $confirm = (string) $helper->ask($input, $output, (new Question('Confirmez le mot de passe : '))->setHidden(true)->setHiddenFallback(false));

        if (mb_strlen($password) < 8) {
            $io->error('Le mot de passe doit contenir au moins 8 caracteres.');

            return Command::FAILURE;
        }

        if ($password !== $confirm) {
            $io->error('Les deux mots de passe ne correspondent pas.');

            return Command::FAILURE;
        }

        $user = $this->usersRepository->findOneBy(['email' => $email]) ?? new Users();
        $isNew = null === $user->getId();

        $user->setEmail($email)
            ->setUsername($username)
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf(
            '%s : %s (%s). Connectez-vous sur /login.',
            $isNew ? 'Administrateur cree' : 'Administrateur mis a jour',
            $username,
            $email
        ));

        return Command::SUCCESS;
    }
}
