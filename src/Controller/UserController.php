<?php

namespace App\Controller;

use App\Entity\Accommodation;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/create', name: 'create_user', methods: ['POST'])]
    public function createUser(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, UserPasswordHasherInterface $passwordHasher): Response
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
    
            // Crear usuario
            $user = new User();
            $user->setName($requestData['name']);
            $user->setSurname($requestData['surname']);
            $user->setEmail($requestData['email']);
            $user->setBirthdate(new \DateTime($requestData['birthday']));
            $user->setHidden(false);
    
            // Procesar avatar si está presente
            if (!empty($requestData['avatar'])) { 
                // Proceso para guardar el avatar  
                $avatarData = explode(',', $requestData['avatar']);
                $avatarBase64 = $avatarData[1];
                $avatarDecoded = base64_decode($avatarBase64);
    
                // Generamos el nombre de la imagen
                $avatarFilename = $slugger->slug($requestData['name']) . '_' . uniqid() . '.jpg';
    
                // Obtener la ruta al directorio público
                $publicDirectory = $this->getParameter('images_avatar');
    
                // Guardar la imagen en el directorio público
                file_put_contents($publicDirectory . $avatarFilename, $avatarDecoded);
    
                // Establecer el nombre del avatar en la entidad de usuario
                $user->setAvatar($avatarFilename);
            } else {
                $user->setAvatar(null);
            }
    
            // Generar contraseña (se recomienda usar bcrypt en lugar de md5)
            $password = password_hash($requestData['password'], PASSWORD_BCRYPT);
            $user->setPassword($password);
    
            // Guardar usuario
            $entityManager->persist($user);
            $entityManager->flush();
    
            // Respuesta JSON
            $data = ['message' => 'Usuario creado'];
            return new JsonResponse($data, JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            // Capturar y manejar la excepción
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
