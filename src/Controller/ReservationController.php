<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Room;
use App\Entity\User;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reservation')]
class ReservationController extends AbstractController
{
    #[Route('/', name: 'app_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservationRepository->findAll(),
        ]);
    }

    #[Route('/create', name: 'app_reservation_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {

        $requestData = json_decode($request->getContent(), true);
        $startDate = DateTime::createFromFormat('D M d Y H:i:s e+', $requestData['startDate']);
        $endDate = DateTime::createFromFormat('D M d Y H:i:s e+', $requestData['endDate']);

        try {
            // crear reserva
            $reservation = new Reservation();
            $reservation->setStartDate($startDate);
            $reservation->setEndDate($endDate);
            $reservation->setStatus($requestData['status']);
            $reservation->setNumberAdults($requestData['numberAdults']);
            $reservation->setPrice($requestData['price']);
            $user = $entityManager->getRepository(User::class)->findOneBy(['id' => intval($requestData['user'])]);
            $room = $entityManager->getRepository(Room::class)->findOneBy(['id' => intval($requestData['room'])]);
            $reservation->setUser($user);
            $reservation->setRoom($room);

            // Guardar usuario
            $entityManager->persist($reservation);
            $entityManager->flush();
            
            $data = ['message' => 'Reserva Realizada'];
            return new JsonResponse($data, JsonResponse::HTTP_CREATED);

        } catch (\Exception $e) {
            // Capturar y manejar la excepción
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/getReservationClient', name: 'app_reservation_client', methods: ['POST'])]
    public function getReservationClient(Request $request, ReservationRepository $reservationRepository): JsonResponse
    {   
                // Obtener el contenido JSON del request
                $idUser = json_decode($request->getContent(), true);
                // Buscar las reservas por el userId
                $reservations = $reservationRepository->findBy(['user' => $idUser]);
                // Transformar las reservas a un array de datos simples
                $reservationData = [];
                foreach ($reservations as $reservation) {
                    $reservationData[] = [
                        'id' => $reservation->getId(),
                        'startDate' => $reservation->getStartDate(),
                        'endDate' => $reservation->getEndDate(),
                        'status' => $reservation->getStatus(),
                        'numberAdults' => $reservation->getNumberAdults(),
                        'price' => $reservation->getPrice(),
                        'room' => $reservation->getRoom()->getId(),
                        'name' => $reservation->getRoom()->getAccommodation()->getName(),
                        'address' => $reservation->getRoom()->getAccommodation()->getAddress(),
                        'postalCode' => $reservation->getRoom()->getAccommodation()->getPostalCode(),
                        'city' => $reservation->getRoom()->getAccommodation()->getCity(),
                        'country' => $reservation->getRoom()->getAccommodation()->getCountry(),
                        'typeAccommodation' => $reservation->getRoom()->getAccommodation()->getTypeAccommodation(),
                        'checkIn' => $reservation->getRoom()->getAccommodation()->getCheckIn(),
                        'checkOut' => $reservation->getRoom()->getAccommodation()->getCheckOut(),
                        'email' => $reservation->getRoom()->getAccommodation()->getEmail(),
                        'city'  => $reservation->getRoom()->getAccommodation()->getCity()->getName(),
                    ];
                }

                // Devolver la respuesta JSON
                return new JsonResponse($reservationData);
    }
    // ---------------------------------- DEFAULT -------------------------------
    #[Route('/new', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}
