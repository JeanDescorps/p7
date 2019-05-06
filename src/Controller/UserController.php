<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    /**
     * Showing user
     * @Route("/users/{id}", name="user_show", methods={"GET"})
     * @param User $user
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function show(User $user, SerializerInterface $serializer) : JsonResponse
    {
        $data = $serializer->serialize($user, 'json', ['groups' => 'user']);
        return new JsonResponse($data, JsonResponse::HTTP_OK, [], false);
    }

    /**
     * Listing user
     * @Route("/users", name="user_list", methods={"GET"})
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function list(UserRepository $userRepository, SerializerInterface $serializer) : JsonResponse
    {
        $users = $userRepository->findAll();
        $data = $serializer->serialize($users, 'json', ['groups' => 'user']);
        return new JsonResponse($data, JsonResponse::HTTP_OK, [], false);
    }

    /**
     * @Route("/{name}/users", name="user_list_client", methods={"GET"})
     * @param Client $client
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function listUserClient(Client $client, UserRepository $userRepository, SerializerInterface $serializer) : JsonResponse
    {
        $users = $userRepository->findBy(['client' => $client->getId()]);
        $data = $serializer->serialize($users, 'json', ['groups' => 'user']);
        return new JsonResponse($data);
    }

    /**
     * User creation
     * @Route("/users", name="user_create", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SerializerInterface $serializer
     * @return Response
     * @throws \Exception
     */
    public function create(Request $request, EntityManagerInterface $manager, SerializerInterface $serializer) : Response
    {
        $data = $serializer->decode($request->getContent(), 'json');
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->submit($data);
        if($form->isSubmitted() && !$form->isValid()) {
            $data = (string)$form->getErrors(true,false);
            return new JsonResponse($data, 400);
        }
        $user->setActive(true)
            ->setRole('ROLE_USER')
            ->setCreatedAt(new DateTime());
        $manager->persist($user);
        $manager->flush();
        return new Response('', Response::HTTP_CREATED);
    }

    /**
     * User update
     * @Route("/users/{id}", name="user_update", methods={"PUT"})
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function update(User $user, Request $request, EntityManagerInterface $manager, SerializerInterface $serializer) : Response
    {
        $data = $serializer->decode($request->getContent(), 'json');
        $form = $this->createForm(UserType::class, $user);
        $form->submit($data);
        if($form->isSubmitted() && !$form->isValid()) {
            $data = (string)$form->getErrors(true,false);
            return new JsonResponse($data, 400);
        }
        $manager->flush();
        return new Response('', Response::HTTP_CREATED);
    }

    /**
     * User delete
     * @Route("/users/{id}", name="user_delete", methods={"DELETE"})
     * @param User $user
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function delete(User $user, EntityManagerInterface $manager) : Response
    {
        $manager->remove($user);
        $manager->flush();
        return new Response('', Response::HTTP_CREATED);
    }
}
