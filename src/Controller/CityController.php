<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\CityType;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface; 

#[Route('/city')]
class CityController extends AbstractController
{
    #[Route('/', name: 'app_city_index', methods: ['GET'])]
    public function index(CityRepository $cityRepository): Response
    {
        return $this->render('city/index.html.twig', [
            'cities' => $cityRepository->findAll(),
        ]);
    }

    #[Route('/all', name: 'app_city_get', methods: ['GET'])]
    public function getCities(CityRepository $cityRepository): JsonResponse
    {

        $cities = $cityRepository->findAll();
    
        $data = [];
        foreach ($cities as $city) {
            $accommodationsData = [];
            foreach ($city->getAccommodations() as $accommodation) {
                $accommodationsData[] = [
                    'id' => $accommodation->getId(),
                    'name' => $accommodation->getName(), // Suponiendo que el alojamiento tiene un método getName() para obtener su nombre
                ];
            }
            
            $data[] = [
                'id' => $city->getId(),
                'name' => $city->getName(),
                'img' => $city->getImg(),
                'accommodations' => $accommodationsData, // Datos de alojamiento (incluyendo ID y nombre)
                // Puedes agregar otros campos relevantes aquí
            ];
        }
    
        return $this->json($data);
    }

    #[Route('/new', name: 'app_city_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $city = new City();
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // img
            $imgFile = $form->get('img')->getData();
            if ($imgFile) {
                $originalFilename = pathinfo($imgFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . uniqid() . '.' . $imgFile->guessExtension();
                // Move the file to the images directory
                try {
                    $imgFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $imgFile->guessExtension();
                    throw new Error("Error al guardar imagen: " . $e->getMessage());
                }
                $city->setImg($newFilename);
            }
            $entityManager->persist($city);
            $entityManager->flush();

            return $this->redirectToRoute('app_city_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('city/new.html.twig', [
            'city' => $city,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_city_show', methods: ['GET'])]
    public function show(City $city): Response
    {
        return $this->render('city/show.html.twig', [
            'city' => $city,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_city_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, City $city, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_city_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('city/edit.html.twig', [
            'city' => $city,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_city_delete', methods: ['POST'])]
    public function delete(Request $request, City $city, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$city->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($city);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_city_index', [], Response::HTTP_SEE_OTHER);
    }
}
