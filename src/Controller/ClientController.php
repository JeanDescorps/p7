<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use App\Service\FormErrors;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ClientController extends AbstractController
{
    /**
     * Showing client
     * @Route("api/clients/{id}", name="client_show", methods={"GET"})
     * @Security("user === client || is_granted('ROLE_ADMIN')")
     * @param Client $client
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function show(Client $client, SerializerInterface $serializer) : JsonResponse
    {
        $data = $serializer->serialize($client, 'json');
        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
    }

    /**
     * Listing client
     * @Route("api/admin/clients", name="client_list", methods={"GET"})
     * @param ClientRepository $clientRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function list(ClientRepository $clientRepository, SerializerInterface $serializer) : JsonResponse
    {
        $clients = $clientRepository->findAll();
        $data = $serializer->serialize($clients, 'json');
        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
    }

    /**
     * Client creation
     * @Route("api/admin/clients", name="client_create", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param FormErrors $formErrors
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $manager, FormErrors $formErrors) : Response
    {
        $data = json_decode($request->getContent(), true);
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->submit($data);
        if($form->isSubmitted() && !$form->isValid()) {
            $errors = $formErrors->getErrors($form);
            return new JsonResponse($errors, 400, [], false);
        }
        $manager->persist($client);
        $manager->flush();
        return new Response('', Response::HTTP_CREATED);
    }

    /**
     * Client update
     * @Route("api/clients/{id}", name="client_update", methods={"PUT"})
     * @Security("user === client || is_granted('ROLE_ADMIN')")
     * @param Client $client
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param FormErrors $formErrors
     * @return Response
     */
    public function update(Client $client, Request $request, EntityManagerInterface $manager, FormErrors $formErrors) : Response
    {
        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(ClientType::class, $client);
        $form->submit($data);
        if($form->isSubmitted() && !$form->isValid()) {
            $errors = $formErrors->getErrors($form);
            return new JsonResponse($errors, 400, [], false);
        }
        $manager->flush();
        return new Response('', Response::HTTP_ACCEPTED);
    }

    /**
     * Client delete
     * @Route("api/admin/clients/{id}", name="client_delete", methods={"DELETE"})
     * @param Client $client
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function delete(Client $client, EntityManagerInterface $manager) : Response
    {
        $manager->remove($client);
        $manager->flush();
        return new Response('', Response::HTTP_ACCEPTED);
    }
}
