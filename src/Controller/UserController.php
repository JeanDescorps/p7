<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\FormErrors;
use App\Service\Pagination;
use App\Service\TableDetails;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UserController
 * @package App\Controller
 * @Route("api/", name="user_")
 */
class UserController extends AbstractController
{
    /**
     * Get details about a specific user
     * @Route("users/{id}", name="show", methods={"GET"})
     * @SWG\Parameter(
     *   name="id",
     *   description="Id of the user to get",
     *   in="path",
     *   required=true,
     *   type="integer"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class))
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="ACCESS DENIED"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="NOT FOUND"
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
        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true, SerializationContext::create()->setGroups(array('Default')));
    }

    /**
     * Get list of all users - Admin only
     * @Route("admin/users", name="list", methods={"GET"})
     * @SWG\Parameter(
     *   name="page",
     *   description="The page number to show",
     *   in="query",
     *   type="integer"
     * )
     * @SWG\Parameter(
     *   name="limit",
     *   description="The number of user per page",
     *   in="query",
     *   type="integer"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *      @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class))
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="ACCESS DENIED"
     * )
     * @SWG\Tag(name="User")
     * @nSecurity(name="Bearer")
     * @param SerializerInterface $serializer
     * @param Request $request
     * @param Pagination $pagination
     * @param TableDetails $tableDetails
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function list(SerializerInterface $serializer, Request $request, Pagination $pagination, TableDetails $tableDetails) : JsonResponse
    {
        $response = new JsonResponse();
        $response->setEtag(md5($tableDetails->lastUpdate('user') . $this->getUser()->getEmail()));
        $response->headers->addCacheControlDirective('no-control');
        $response->setPublic();

        if($response->isNotModified($request)) {
            return $response;
        }

        $limit = $request->query->get('limit', $this->getParameter('default_user_limit'));
        $page = $request->query->get('page', 1);
        $route = $request->attributes->get('_route');

        $pagination->setEntityClass(User::class)
            ->setRoute($route);
        $pagination->setCurrentPage($page)
            ->setLimit($limit);

        $paginated = $pagination->getData();
        $data = $serializer->serialize($paginated, 'json', SerializationContext::create()->setGroups(array('Default')));

        $response->setJson($data);

        return $response;
    }

    /**
     * Get users client list
     * @Route("users", name="list_client", methods={"GET"})
     * @SWG\Parameter(
     *   name="page",
     *   description="The page number to show",
     *   in="query",
     *   type="integer"
     * )
     * @SWG\Parameter(
     *   name="limit",
     *   description="The number of user per page",
     *   in="query",
     *   type="integer"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *      @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class))
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @SWG\Tag(name="User")
     * @nSecurity(name="Bearer")
     * @param SerializerInterface $serializer
     * @param Request $request
     * @param Pagination $pagination
     * @param UserRepository $userRepository
     * @return JsonResponse
     */
    public function listUserClient(SerializerInterface $serializer, Request $request, Pagination $pagination, UserRepository $userRepository) : JsonResponse
    {
        $response = new JsonResponse();
        $userClient = $serializer->serialize($userRepository->findBy(['client' => $this->getUser()]), 'json');
        $response->setEtag(md5($userClient . $this->getUser()->getEmail()));
        $response->headers->addCacheControlDirective('no-control');
        $response->setPublic();

        if($response->isNotModified($request)) {
            return $response;
        }

        $limit = $request->query->get('limit', $this->getParameter('default_user_limit'));
        $page = $request->query->get('page', 1);
        $route = $request->attributes->get('_route');

        $pagination->setEntityClass(User::class)
            ->setRoute($route);
        $pagination->setCurrentPage($page)
            ->setLimit($limit);
        $pagination->setCriteria(['client' => $this->getUser()]);

        $paginated = $pagination->getData();
        $data = $serializer->serialize($paginated, 'json', SerializationContext::create()->setGroups(array('Default')));

        $response->setJson($data);

        return $response;
    }

    /**
     * User creation
     * @Route("users", name="create", methods={"POST"})
     * @SWG\Parameter(
     *   name="User",
     *   description="Fields to provide to create an user",
     *   in="body",
     *   required=true,
     *   type="string",
     *   @SWG\Schema(
     *     type="object",
     *     title="User field",
     *     @SWG\Property(property="username", type="string"),
     *     @SWG\Property(property="email", type="string")
     *     )
     * )
     * @SWG\Response(
     *     response=201,
     *     description="CREATED",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class))
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="BAD REQUEST"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @SWG\Tag(name="User")
     * @nSecurity(name="Bearer")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @return JsonResponse
     * @throws \Exception
     */
    public function create(Request $request, EntityManagerInterface $manager, SerializerInterface $serializer, ValidatorInterface $validator) : JsonResponse
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $errors = $validator->validate($user);
        if(count($errors) > 0) {
            $data = $serializer->serialize($errors, 'json');
            return new JsonResponse($data, 400, [], true);
        }
        $user->setCreatedAt(new DateTime())
            ->setClient($this->getUser());
        $manager->persist($user);
        $manager->flush();
        $data = $serializer->serialize($user, 'json', SerializationContext::create()->setGroups(array('Default')));
        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    /**
     * User update
     * @Route("users/{id}", name="update", methods={"PUT"})
     * @SWG\Parameter(
     *   name="id",
     *   description="Id of the user to update",
     *   in="path",
     *   required=true,
     *   type="integer"
     * )
     * @SWG\Parameter(
     *   name="User",
     *   description="Fields to provide to update an user",
     *   in="body",
     *   required=true,
     *   type="string",
     *   @SWG\Schema(
     *     type="object",
     *     title="User field",
     *     @SWG\Property(property="username", type="string"),
     *     @SWG\Property(property="email", type="string")
     *     )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class))
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="BAD REQUEST"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="ACCESS DENIED"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="NOT FOUND"
     * )
     * @SWG\Tag(name="User")
     * @nSecurity(name="Bearer")
     * @Security("user === userC.getClient() || is_granted('ROLE_ADMIN')")
     * @param User $userC
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param FormErrors $formErrors
     * @param SerializerInterface $serializer
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(User $userC, Request $request, EntityManagerInterface $manager, FormErrors $formErrors, SerializerInterface $serializer) : JsonResponse
    {
        $data = json_decode($request->getContent(), 'json');
        // Use symfony/forms for update @see https://github.com/schmittjoh/JMSSerializerBundle/issues/575#issuecomment-303058694
        $form = $this->createForm(UserType::class, $userC);
        $form->submit($data);
        if($form->isSubmitted() && !$form->isValid()) {
            $errors = $formErrors->getErrors($form);
            return new JsonResponse($errors, 400, [], false);
        }
        $userC->setUpdatedAt(new DateTime());
        $manager->flush();
        $data = $serializer->serialize($userC, 'json', SerializationContext::create()->setGroups(array('Default')));
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * User deletion
     * @Route("users/{id}", name="delete", methods={"DELETE"})
     * @SWG\Parameter(
     *   name="id",
     *   description="Id of the user to delete",
     *   in="path",
     *   required=true,
     *   type="integer"
     * )
     * @SWG\Response(
     *     response=204,
     *     description="NO CONTENT"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="ACCESS DENIED"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="NOT FOUND"
     * )
     * @SWG\Tag(name="User")
     * @nSecurity(name="Bearer")
     * @Security("user === userC.getClient() || is_granted('ROLE_ADMIN')")
     * @param User $user
     * @param EntityManagerInterface $manager
     * @return JsonResponse
     */
    public function delete(User $userC, EntityManagerInterface $manager) : JsonResponse
    {
        $manager->remove($userC);
        $manager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
