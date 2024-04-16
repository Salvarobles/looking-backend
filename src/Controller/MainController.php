<?php

namespace App\Controller;

use App\Entity\Accommodation;
use App\Entity\User;
use App\Repository\AccommodationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(): Response
    {
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/validateAccount', name: 'app_main_validateAccount')]
    public function validateAccount(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, AccommodationRepository $accommodationRepository): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        try {
            // Encontrar si existe el email.
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $requestData['email']]);
            $existingAccommodation = $entityManager->getRepository(Accommodation::class)->findOneBy(['email' => $requestData['email']]);

            if ($existingUser) {
               $userPassword = $existingUser -> getPassword();

               if (password_verify($requestData['password'], $userPassword)) {
                $user=$userRepository->findOneBy(['email' => $requestData['email']]);

                    if ($user->isHidden()) {
                        throw new \Exception('Usuario bloqueado, contacta con nuestro servicio.');
                    }

                    // Obtener la ruta del archivo
                    $avatarFilePath = $user->getAvatar();

                    // Obtener la ruta al directorio público
                    $publicDirectory = $this->getParameter('images_avatar');

                    // Leer el contenido del archivo
                    $avatarContent = file_get_contents($publicDirectory . $avatarFilePath);

                    // Convertir el contenido del archivo a Base64
                    $avatarBase64 = base64_encode($avatarContent);
                
                $userData = [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'name' => $user->getName(),
                    'surname' => $user->getSurname(),
                    'birthday' => $user->getBirthdate(),
                    'reservations' => $user->getReservations(),
                    'reviews' => $user->getReviews(),
                    'avatar' => $avatarBase64,
                    'rol' => 'user',
                ];

                $data = ['login' => 'true', 'account' => $userData];
                return new JsonResponse($data, JsonResponse::HTTP_CREATED);
                } else {
                    throw new \Exception('Cuenta no encontrada no encontrado.');
                }
            }

            if ($existingAccommodation) {

                $accommodationPassword = $existingAccommodation -> getPassword();

                if (password_verify($requestData['password'], $accommodationPassword)) {

                    $accommodation=$accommodationRepository->findOneBy(['email' => $requestData['email']]);

                    if ($accommodation->isHidden()) {
                        throw new \Exception('Si su alojamiento ha sido recientemente creado, le solicitamos su comprensión mientras nuestro equipo de administración lo revisa. Si considera que ha pasado un tiempo excesivo sin respuesta, le invitamos a contactar a nuestro servicio de atención al cliente para asistencia adicional.');
                    }

                    // Obtener las rutsa de los archivos
                    $imgsFilePaths = $accommodation->getImg();

                    // Array para almacenar los datos Base64 de las imágenes
                    $imgsBase64 = [];

                    // Recorrer cada ruta de archivo
                    foreach ($imgsFilePaths as $imgFilePath) {
                        // Leer el contenido del archivo
                        $imgContent = file_get_contents($imgFilePath);
                        
                        // Convertir el contenido del archivo a Base64
                        $imgBase64 = base64_encode($imgContent);
                        
                        // Agregar el dato Base64 al array de imágenes
                        $imgsBase64[] = $imgBase64;
                    }


                    $accommodationData = [
                        'id' => $accommodation->getId(),
                        'name' => $accommodation->getName(),
                        'email' => $accommodation->getEmail(),
                        'address' => $accommodation->getAddress(),
                        'country' => $accommodation->getCountry(),
                        'postalCode' => $accommodation->getPostalCode(),
                        'typeAccommodation' => $accommodation->getTypeAccommodation(),
                        'numberRooms' => $accommodation->getNumberRooms(),
                        'services' => $accommodation->getServices(),
                        'img' => $imgBase64,
                        'checkIn' => $accommodation->getCheckIn(),
                        'checkOut' => $accommodation->getCheckOut(),
                        'description' => $accommodation->getDescription(),
                        'rooms' => $accommodation->getCity(),
                        'reviews' => $accommodation->getReviews(),
                        'rol' => 'accommodation',
                    ];

                    $data = ['login' => 'true', 'account' => $accommodationData];

                 return new JsonResponse($data, JsonResponse::HTTP_CREATED);
                 } else {
                     throw new \Exception('Cuenta no encontrada no encontrado.');
                 }
             }


        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }

    }

    // ARREGLAR LA SINTAXISSS

    #[Route('/getAccount', name: 'app_main_getAccount')]
    public function getAccount(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, AccommodationRepository $accommodationRepository): JsonResponse
    {
        $email = json_decode($request->getContent(), true);

        try {
            // Encontrar si existe el email.
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            $existingAccommodation = $entityManager->getRepository(Accommodation::class)->findOneBy(['email' => $email]);

            if ($existingUser) {

                $user=$userRepository->findOneBy(['email' => $email]);

                    if ($user->isHidden()) {
                        throw new \Exception('Usuario bloqueado, contacta con nuestro servicio.');
                    }

                    // Obtener la ruta del archivo
                    $avatarFilePath = $user->getAvatar();

                    // Obtener la ruta al directorio público
                    $publicDirectory = $this->getParameter('images_avatar');

                    // Leer el contenido del archivo
                    $avatarContent = file_get_contents($publicDirectory . $avatarFilePath);

                    // Convertir el contenido del archivo a Base64
                    $avatarBase64 = base64_encode($avatarContent);
                
                $userData = [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'name' => $user->getName(),
                    'surname' => $user->getSurname(),
                    'birthday' => $user->getBirthdate(),
                    'reservations' => $user->getReservations(),
                    'reviews' => $user->getReviews(),
                    'avatar' => $avatarBase64,
                    'rol' => 'user',
                ];

                $data = ['account' => $userData];
                return new JsonResponse($data, JsonResponse::HTTP_CREATED);
                } else {
                    throw new \Exception('Cuenta no encontrada no encontrado.');
                }
            

            if ($existingAccommodation) {

                    $accommodation=$accommodationRepository->findOneBy(['email' => $email]);

                    if ($accommodation->isHidden()) {
                        throw new \Exception('Si su alojamiento ha sido recientemente creado, le solicitamos su comprensión mientras nuestro equipo de administración lo revisa. Si considera que ha pasado un tiempo excesivo sin respuesta, le invitamos a contactar a nuestro servicio de atención al cliente para asistencia adicional.');
                    }

                    // Obtener las rutsa de los archivos
                    $imgsFilePaths = $accommodation->getImg();

                    // Array para almacenar los datos Base64 de las imágenes
                    $imgsBase64 = [];

                    // Recorrer cada ruta de archivo
                    foreach ($imgsFilePaths as $imgFilePath) {
                        // Leer el contenido del archivo
                        $imgContent = file_get_contents($imgFilePath);
                        
                        // Convertir el contenido del archivo a Base64
                        $imgBase64 = base64_encode($imgContent);
                        
                        // Agregar el dato Base64 al array de imágenes
                        $imgsBase64[] = $imgBase64;
                    }


                    $accommodationData = [
                        'id' => $accommodation->getId(),
                        'name' => $accommodation->getName(),
                        'email' => $accommodation->getEmail(),
                        'address' => $accommodation->getAddress(),
                        'country' => $accommodation->getCountry(),
                        'postalCode' => $accommodation->getPostalCode(),
                        'typeAccommodation' => $accommodation->getTypeAccommodation(),
                        'numberRooms' => $accommodation->getNumberRooms(),
                        'services' => $accommodation->getServices(),
                        'img' => $imgBase64,
                        'checkIn' => $accommodation->getCheckIn(),
                        'checkOut' => $accommodation->getCheckOut(),
                        'description' => $accommodation->getDescription(),
                        'rooms' => $accommodation->getCity(),
                        'reviews' => $accommodation->getReviews(),
                        'rol' => 'accommodation',
                    ];

                    $data = ['login' => 'true', 'account' => $accommodationData];

                 return new JsonResponse($data, JsonResponse::HTTP_CREATED);
                 } else {
                     throw new \Exception('Cuenta no encontrada no encontrado.');
                 }
             }
            
         catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }

    }
}
