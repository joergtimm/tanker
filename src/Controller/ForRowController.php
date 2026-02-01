<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Move;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class ForRowController extends AbstractController
{
    #[Route('/for/row', name: 'app_for_row')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('games/four/four.html.twig', [
            'controller_name' => 'ForRowController',
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/for/row/start', name: 'app_for_row_start', methods: ['POST'])]
    public function startGame(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $player2Id = $request->request->get('player2');
        $game = new Game();
        $game->setPlayer1($this->getUser());

        if ($player2Id) {
            $player2 = $userRepository->find($player2Id);
            $game->setPlayer2($player2);
        }

        $entityManager->persist($game);
        $entityManager->flush();

        return $this->redirectToRoute('app_for_row_game', ['id' => $game->getId()]);
    }

    #[Route('/for/row/game/{id}', name: 'app_for_row_game')]
    public function game(Game $game): Response
    {
        return $this->render('games/four/four.html.twig', [
            'game' => $game,
        ]);
    }

    #[Route('/for/row/game/{id}/move', name: 'app_for_row_move', methods: ['POST'])]
    public function makeMove(Game $game, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $column = $data['column'] ?? null;
        $boardIndex = $data['boardIndex'] ?? null;
        $playerNumber = $data['playerNumber'] ?? null;

        if ($column === null || $boardIndex === null || $playerNumber === null) {
            return new JsonResponse(['error' => 'Invalid data'], 400);
        }

        $move = new Move();
        $move->setGame($game);
        $move->setColumnNumber($column);
        $move->setBoardIndex($boardIndex);
        $move->setPlayerNumber($playerNumber);
        $move->setBoardState($data['allPieces'] ?? null);

        if ($playerNumber === 1) {
            $move->setPlayer($game->getPlayer1());
        } elseif ($playerNumber === 2 && $game->getPlayer2()) {
            $move->setPlayer($game->getPlayer2());
        }

        $entityManager->persist($move);
        $entityManager->flush();

        return new JsonResponse(['status' => 'success']);
    }
}
