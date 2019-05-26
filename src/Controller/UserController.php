<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\FormErrors;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Security as nSecurity;

class UserController extends AbstractController
{
    /**
     * Showing user
     * @Route("api/users/{id}", name="user_show", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Return json array with user's details",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class))
     *     )
     * )
     * @SWG\Tag(name="User")
     * @nSecurity(name="Bearer")
     * @Security("user === userC.getClient() || is_granted('ROLE_ADMIN')")
     * @param User $userC
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function show(User $userC, SerializerInterface $serializer) : JsonResponse
    {
        $data = $serializer->serialize($userC, 'json');
        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
    }

    /**
     * Listing all users - Admin only
     * @Route("api/admin/users", name="user_list", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Return json array with all the users"
     * )
     * @SWG\Tag(name="User")
     * @nSecurity(name="Bearer")
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function list(UserRepository $userRepository, SerializerInterface $serializer) : JsonResponse
    {
        $users = $userRepository->findAll();
        $data = $serializer->serialize($users, 'json');
        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
    }

    /**
     * Listing client's users
     * @Route("api/users", name="user_list_client", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Return a json array with all the users bounded to the connected client"
     * )
     * @SWG\Tag(name="User")
     * @nSecurity(name="Bearer")
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function listUserClient(UserRepository $userRepository, SerializerInterface $serializer) : JsonResponse
    {
        $users = $userRepository->findBy(['client' => $this->getUser()]);
        $data = $serializer->serialize($users, 'json');
        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
    }

    /**
     * User creation
     * @Route("api/users", name="user_create", methods={"POST"})
     * @SWG\Parameter(
     *   name="body",
     *   in="body",
     *   required=true,
     *   @SWG\Schema(
     *     type="object",
     *     title="Mobile field",
     *     @SWG\Property(property="username", type="string"),
     *     @SWG\Property(property="email", type="string")
     *     )
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Create an user bound to the connected client"
     * )
     * @SWG\Tag(name="User")
     * @nSecurity(name="Bearer")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param FormErrors $formErrors
     * @return Response
     * @throws \Exception
     */
    public function create(Request $request, EntityManagerInterface $manager, FormErrors $formErrors) : Response
    {
        $data = json_decode($request->getContent(), true);
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->submit($data);
        if($form->isSubmitted() && !$form->isValid()) {
            $errors = $formErrors->getErrors($form);
            return new JsonResponse($errors, 400, [], false);
        }
        $user->setCreatedAt(new DateTime())
            ->setClient($this->getUser());
        $manager->persist($user);
        $manager->flush();
        return new Response('', Response::HTTP_CREATED);
    }

    /**
     * User update
     * @Route("api/users/{id}", name="user_update", methods={"PUT"})
     * @SWG\Parameter(
     *   name="body",
     *   in="body",
     *   required=true,
     *   @SWG\Schema(
     *     type="object",
     *     title="Mobile field",
     *     @SWG\Property(property="username", type="string"),
     *     @SWG\Property(property="email", type="string")
     *     )
     * )
     * @SWG\Response(
     *     response=202,
     *     description="Update an user"
     * )
     * @SWG\Tag(name="User")
     * @nSecurity(name="Bearer")
     * @Security("user === userC.getClient() || is_granted('ROLE_ADMIN')")
     * @param User $userC
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param FormErrors $formErrors
     * @return Response
     * @throws \Exception
     */
    public function update(User $userC, Request $request, EntityManagerInterface $manager, FormErrors $formErrors) : Response
    {
        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(UserType::class, $userC);
        $form->submit($data);
        if($form->isSubmitted() && !$form->isValid()) {
            $errors = $formErrors->getErrors($form);
            return new JsonResponse($errors, 400, [], false);
        }
        $userC->setUpdatedAt(new DateTime());
        $manager->flush();
        return new Response('', Response::HTTP_ACCEPTED);
    }

    /**
     * User delete
     * @Route("api/users/{id}", name="user_delete", methods={"DELETE"})
     * @SWG\Response(
     *     response=202,
     *     description="Delete an user"
     * )
     * @SWG\Tag(name="User")
     * @nSecurity(name="Bearer")
     * @Security("user === user.getClient() || is_granted('ROLE_ADMIN')")
     * @param User $user
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function delete(User $user, EntityManagerInterface $manager) : Response
    {
        $manager->remove($user);
        $manager->flush();
        return new Response('', Response::HTTP_ACCEPTED);
    }
}
