<?php

namespace App\Controller;

use App\Entity\Accommodation;
use App\Entity\City;
use App\Entity\Room;
use App\Entity\User;
use App\Form\AccommodationType;
use App\Repository\AccommodationRepository;
use App\Repository\CityRepository;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/accommodation')]
class AccommodationController extends AbstractController
{
    #[Route('/', name: 'app_accommodation_index', methods: ['GET'])]
    public function index(AccommodationRepository $accommodationRepository): Response
    {
        return $this->render('accommodation/index.html.twig', [
            'accommodations' => $accommodationRepository->findAll(),
        ]);
    }

    #[Route('/alltypes', name: 'app_accommodation_types', methods: ['GET'])]
    public function getAllTypes(): JsonResponse
    {
        $accommodation = new Accommodation();
        $types = $accommodation->getTypesAccommodations();
    
        return $this->json($types);
    }

    #[Route('/create', name: 'create_accommodation', methods: ['POST'])]
    public function createUser(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger,  UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        try {
            // Comprobar si el email ya existe
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $requestData['email']]);
            $existingAccommodation = $entityManager->getRepository(Accommodation::class)->findOneBy(['email' => $requestData['email']]);
            if ($existingUser !== null) {
                throw new \Exception('El email esta en uso');
            }
            if ($existingAccommodation !== null) {
                throw new \Exception('El email esta en uso');
            }
    
            // Crear accomodation
            $accommodation = new Accommodation();
            $accommodation->setName($requestData['name']);
            $accommodation->setAddress($requestData['address']);
            $accommodation->setCountry('España');
            $accommodation->setPostalCode(intval($requestData['postalCode']));
            $accommodation->setTypeAccommodation($requestData['typeAccommodation']);
            $accommodation->setNumberRooms(intval($requestData['numberRooms']));
            $accommodation->setEmail($requestData['email']);
            $accommodation->setCheckIn(new \DateTime($requestData['checkIn']));
            $accommodation->setCheckOut(new \DateTime($requestData['checkOut']));
            $accommodation->setDescription($requestData['description']);

            $accommodation->setHidden(true);
    
            // Procesar las fotos si están presentes
            if (!empty($requestData['img']) && is_array($requestData['img'])) { 
                $imgArray = [];

                foreach ($requestData['img'] as $index => $imgBase64) {
                    // Proceso para guardar la imagen
                    $imgData = explode(',', $imgBase64);
                    $imgBase64 = $imgData[1];
                    $imgDecoded = base64_decode($imgBase64);

                    // Generamos el nombre de la imagen
                    $imgFilename = $slugger->slug($requestData['name']) . '_' . uniqid() . '.jpg';

                    // Obtener la ruta al directorio público
                    $publicDirectory = $this->getParameter('images_accommodation');

                    // Guardar la imagen en el directorio público
                    file_put_contents($publicDirectory . $imgFilename, $imgDecoded);

                    // Agregar el nombre del archivo al array
                    $imgArray[] = $imgFilename;
                }

                // Establecer el array de imágenes en la entidad de alojamiento
                $accommodation->setImg($imgArray);
            } else {
                $accommodation->setImg(null);
            }

            // Relacion con city
            $city = $entityManager->getRepository(City::class)->findOneBy(['id' => intval($requestData['city'])]);

            $accommodation->setCity($city);

            // Crear las rooms

            if (!empty($requestData['numberRooms']) && !empty($requestData['maximumCapacity'])) {
                $numberRooms = intval($requestData['numberRooms']);
                $maximumCapacity = intval($requestData['maximumCapacity']);
                if (is_numeric($numberRooms) && is_numeric($maximumCapacity)) {
                    $accommodation->createRooms($numberRooms, $maximumCapacity);
                } else {
                    throw new \Exception('No es un numero habitaciones');
                }
            } else {
                throw new \Exception('Error al crear las habitaciones', $requestData['numberRooms'] );
            }
    
            // Generar contraseña
            // $password = hash('sha256', $requestData['password']);
            $password = password_hash($requestData['password'], PASSWORD_BCRYPT);
            $accommodation->setPassword($password);
    
            // Guardar usuario
            $entityManager->persist($accommodation);
            $entityManager->flush();
    
            // Respuesta JSON
            $data = ['message' => 'Alojamiento creado'];
            return new JsonResponse($data, JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            // Capturar y manejar la excepción
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/new', name: 'app_accommodation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $accommodation = new Accommodation();
        $form = $this->createForm(AccommodationType::class, $accommodation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($accommodation);
            $entityManager->flush();

            return $this->redirectToRoute('app_accommodation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('accommodation/new.html.twig', [
            'accommodation' => $accommodation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_accommodation_show', methods: ['GET'])]
    public function show(Accommodation $accommodation): Response
    {
        return $this->render('accommodation/show.html.twig', [
            'accommodation' => $accommodation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_accommodation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Accommodation $accommodation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AccommodationType::class, $accommodation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_accommodation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('accommodation/edit.html.twig', [
            'accommodation' => $accommodation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_accommodation_delete', methods: ['POST'])]
    public function delete(Request $request, Accommodation $accommodation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$accommodation->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($accommodation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_accommodation_index', [], Response::HTTP_SEE_OTHER);
    }
}
