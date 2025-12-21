<?php

namespace App\Controller;

use App\Entity\Station;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class FavoriteController extends AbstractController
{
    #[Route('/favorite/toggle/{uuid}', name: 'app_favorite_toggle', methods: ['POST'])]
    public function toggle(string $uuid, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $station = $entityManager->getRepository(Station::class)->findOneBy(['uuid' => $uuid]);

        if (!$station) {
            return new JsonResponse(['error' => 'Station not found'], 404);
        }

        if ($user->getFavorites()->contains($station)) {
            $user->removeFavorite($station);
            $isFavorite = false;
        } else {
            $user->addFavorite($station);
            $isFavorite = true;
        }

        $entityManager->flush();

        return new JsonResponse(['isFavorite' => $isFavorite]);
    }

    #[Route('/favorites', name: 'app_favorites')]
    public function list(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('favorite/index.html.twig', [
            'stations' => $user->getFavorites(),
        ]);
    }
}
