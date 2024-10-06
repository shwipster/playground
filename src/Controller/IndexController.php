<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private RequestStack $requestStack,

    ) {}

    //#[Route('/{uri}', name: 'app_index', requirements: ['uri' => '((?!\.).)*'], utf8: true)]
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }

    #[Route('/users/{userName}')]
    public function users(string $userName = ''): Response
    {
        if ($userName) {
            $email = "$userName@test.ee";
            /** @var User */
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if (!$user) {
                $user = new User();
                $user->setEmail($email);
                $user->setName($userName);
                $user->setLastname('Perenimi');

                $hashedPassword = $this->passwordHasher->hashPassword(
                    $user,
                    "1234"
                );
                $user->setPassword($hashedPassword);

                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }
        }


        /** @var User[]  */
        $contents = $this->entityManager->getRepository(User::class)->findAll();
        $arr = [];
        foreach ($contents as $content) {
            $arr[] = [
                "id" => $content->getId(),
                "name" => $content->getName(),
                "email" => $content->getEmail(),
            ];
        }

        return $this->render('index/users.html.twig', [
            'users' => $arr
        ]);
    }
}
