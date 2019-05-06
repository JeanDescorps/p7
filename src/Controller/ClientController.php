<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ClientController extends AbstractController
{
    /**
     * Showing client
     * @Route("/clients/{id}", name="client_show", methods={"GET"})
     * @param Client $client
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function show(Client $client, SerializerInterface $serializer) : JsonResponse
    {
        $data = $serializer->serialize($client, 'json', ['groups' => 'client']);
        return new JsonResponse($data);
    }

    /**
     * Listing mobile
     * @Route("/clients", name="client_list", methods={"GET"})
     * @param ClientRepository $clientRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function list(ClientRepository $clientRepository, SerializerInterface $serializer) : JsonResponse
    {
        $clients = $clientRepository->findAll();
        $data = $serializer->serialize($clients, 'json', ['groups' => 'client']);
        return new JsonResponse($data);
    }

    /**
     * Client creation
     * @Route("/clients", name="client_create", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $manager, SerializerInterface $serializer) : Response
    {
        $data = $serializer->decode($request->getContent(), 'json');
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->submit($data);
        if($form->isSubmitted() && !$form->isValid()) {
            $data = (string)$form->getErrors(true,false);
            return new JsonResponse($data, 400);
        }
        $manager->persist($client);
        $manager->flush();
        return new Response('', Response::HTTP_CREATED);
    }

    /**
     * Client update
     * @Route("/clients/{id}", name="client_update", methods={"PUT"})
     * @param Client $client
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function update(Client $client, Request $request, EntityManagerInterface $manager, SerializerInterface $serializer) : Response
    {
        $data = $serializer->decode($request->getContent(), 'json');
        $form = $this->createForm(ClientType::class, $client);
        $form->submit($data);
        if($form->isSubmitted() && !$form->isValid()) {
            $data = (string)$form->getErrors(true,false);
            return new JsonResponse($data, 400);
        }
        $manager->flush();
        return new Response('', Response::HTTP_CREATED);
    }

    /**
     * Client delete
     * @Route("/clients/{id}", name="client_delete", methods={"DELETE"})
     * @param Client $client
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function delete(Client $client, EntityManagerInterface $manager)
    {
        $manager->remove($client);
        $manager->flush();
        return new Response('', Response::HTTP_CREATED);
    }
}
