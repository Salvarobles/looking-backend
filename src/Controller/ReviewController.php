<?php

namespace App\Controller;

use App\Entity\Accommodation;
use App\Entity\Review;
use App\Entity\User;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/review')]
class ReviewController extends AbstractController
{

    #[Route('/createReview', name: 'app_review_create', methods: ['POST'])]
    public function createReview(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $errors = [];

        // Validar rating
        if (!isset($data['rating']) || !is_int($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
            $errors['rating'] = 'Rating debe de ser del 1 al 5.';
        }

        // Validar comment
        if (!isset($data['comment']) || !is_string($data['comment']) || empty($data['comment'])) {
            $errors['comment'] = 'No debe de estar vacio el campo comentario.';
        }

        // Validar date
        $date = DateTime::createFromFormat('D M d Y H:i:s e+', $data['date']);
        if (!isset($data['date']) || !$date) {
            $errors['date'] = 'La fecha no es valida.';
        }

        // Validar idUser
        if (!isset($data['idUser']) || !is_int($data['idUser'])) {
            $errors['idUser'] = 'El id de usuario no es valida.';
        } else {
            $user = $entityManager->getRepository(User::class)->find($data['idUser']);
            if (!$user) {
                $errors['idUser'] = 'Usuario no encontrado.';
            }
        }


        // Validar idAccommodation
        if (!isset($data['idAccommodation']) || !is_int($data['idAccommodation'])) {
            $errors['idAccommodation'] = 'El id del alojamiento no es valida.';
        } else {
            $accommodation = $entityManager->getRepository(Accommodation::class)->find($data['idAccommodation']);
            if (!$accommodation) {
                $errors['idAccommodation'] = 'Accommodation not found.';
            }
        }

        if (!empty($errors)) {
            return new JsonResponse(['errors' => $errors], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Crear la entidad Review
        $review = new Review();
        $review->setRating($data['rating']);
        $review->setComment($data['comment']);
        $review->setDate($date);
        $review->setUser($user);
        $review->setAccommodation($accommodation);

        // Persistir y guardar la entidad
        $entityManager->persist($review);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Review created successfully'], JsonResponse::HTTP_CREATED);
    }



    // ------------------------------------------------- default -------------------------------
    #[Route('/', name: 'app_review_index', methods: ['GET'])]
    public function index(ReviewRepository $reviewRepository): Response
    {
        return $this->render('review/index.html.twig', [
            'reviews' => $reviewRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_review_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($review);
            $entityManager->flush();

            return $this->redirectToRoute('app_review_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('review/new.html.twig', [
            'review' => $review,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_review_show', methods: ['GET'])]
    public function show(Review $review): Response
    {
        return $this->render('review/show.html.twig', [
            'review' => $review,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_review_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Review $review, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_review_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('review/edit.html.twig', [
            'review' => $review,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_review_delete', methods: ['POST'])]
    public function delete(Request $request, Review $review, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$review->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($review);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_review_index', [], Response::HTTP_SEE_OTHER);
    }
}
