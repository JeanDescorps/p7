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
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Security as nSecurity;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ClientController extends AbstractController
{
    /**
     * Showing client
     * @Route("api/clients/{id}", name="client_show", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Return json array with client's details",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Client::class))
     *     )
     * )
     * @SWG\Tag(name="Client")
     * @nSecurity(name="Bearer")
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
     * Listing client - Admin only
     * @Route("api/admin/clients", name="client_list", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Return json array with all clients"
     * )
     * @SWG\Tag(name="Client")
     * @nSecurity(name="Bearer")
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
     * Client creation - Admin only
     * @Route("api/admin/clients", name="client_create", methods={"POST"})
     * @SWG\Parameter(
     *   name="body",
     *   in="body",
     *   required=true,
     *   @SWG\Schema(
     *     type="object",
     *     title="Client field",
     *     @SWG\Property(property="name", type="string"),
     *     @SWG\Property(property="email", type="string"),
     *     @SWG\Property(property="password", type="string")
     *     )
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Create a client"
     * )
     * @SWG\Tag(name="Client")
     * @nSecurity(name="Bearer")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param FormErrors $formErrors
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $manager, FormErrors $formErrors, UserPasswordEncoderInterface $encoder) : Response
    {
        $data = json_decode($request->getContent(), true);
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->submit($data);
        if($form->isSubmitted() && !$form->isValid()) {
            $errors = $formErrors->getErrors($form);
            return new JsonResponse($errors, 400, [], false);
        }
        $password = $encoder->encodePassword($client, $client->getPassword());
        $client->setPassword($password);
        $manager->persist($client);
        $manager->flush();
        return new Response('', Response::HTTP_CREATED);
    }

    /**
     * Client update
     * @Route("api/clients/{id}", name="client_update", methods={"PUT"})
     * @SWG\Parameter(
     *   name="body",
     *   in="body",
     *   required=true,
     *   @SWG\Schema(
     *     type="object",
     *     title="Client field",
     *     @SWG\Property(property="name", type="string"),
     *     @SWG\Property(property="email", type="string"),
     *     @SWG\Property(property="password", type="string")
     *     )
     * )
     * @SWG\Response(
     *     response=202,
     *     description="Update a client"
     * )
     * @SWG\Tag(name="Client")
     * @nSecurity(name="Bearer")
     * @Security("user === client || is_granted('ROLE_ADMIN')")
     * @param Client $client
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param FormErrors $formErrors
     * @param UserPasswordEncoderInterface $encoder;
     * @return Response
     */
    public function update(Client $client, Request $request, EntityManagerInterface $manager, FormErrors $formErrors, UserPasswordEncoderInterface $encoder) : Response
    {
        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(ClientType::class, $client);
        $form->submit($data);
        if($form->isSubmitted() && !$form->isValid()) {
            $errors = $formErrors->getErrors($form);
            return new JsonResponse($errors, 400, [], false);
        }
        $password = $encoder->encodePassword($client, $client->getPassword());
        $client->setPassword($password);
        $manager->flush();
        return new Response('', Response::HTTP_ACCEPTED);
    }

    /**
     * Client delete - Admin only
     * @Route("api/admin/clients/{id}", name="client_delete", methods={"DELETE"})
     * @SWG\Response(
     *     response=202,
     *     description="Delete a client"
     * )
     * @SWG\Tag(name="Client")
     * @nSecurity(name="Bearer")
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
