<?php

namespace App\Controller;

use App\Form\MobileType;
use App\Service\FormErrors;
use App\Service\Pagination;
use App\Service\TableDetails;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface;
use App\Entity\Mobile;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Security as nSecurity;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class MobileController
 * @package App\Controller
 * @Route("api/", name="mobile_")
 */
class MobileController extends AbstractController
{
    /**
     * Get details about a specific mobile
     * @Route("mobiles/{id}", name="show", methods={"GET"})
     * @SWG\Parameter(
     *   name="id",
     *   description="Id of the mobile to get",
     *   in="path",
     *   required=true,
     *   type="integer"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Mobile::class))
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="NOT FOUND"
     * )
     * @SWG\Tag(name="Mobile")
     * @nSecurity(name="Bearer")
     * @param Mobile $mobile
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function show(Mobile $mobile, SerializerInterface $serializer) : JsonResponse
    {
        $data = $serializer->serialize($mobile, 'json');
        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
    }

    /**
     * Get mobiles list
     * @Route("mobiles", name="list", methods={"GET"})
     * @SWG\Parameter(
     *   name="page",
     *   description="The page number to show",
     *   in="query",
     *   type="integer"
     * )
     * @SWG\Parameter(
     *   name="limit",
     *   description="The number of mobile per page",
     *   in="query",
     *   type="integer"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *      @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Mobile::class))
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @SWG\Tag(name="Mobile")
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
        $response->setEtag(md5($tableDetails->lastUpdate('mobile') . $this->getUser()->getEmail()));
        $response->headers->addCacheControlDirective('no-control');
        $response->setPublic();

        if($response->isNotModified($request)) {
            return $response;
        }

        $limit = $request->query->get('limit', $this->getParameter('default_mobile_limit'));
        $page = $request->query->get('page', 1);
        $route = $request->attributes->get('_route');

        $pagination->setEntityClass(Mobile::class)
            ->setRoute($route);
        $pagination->setCurrentPage($page)
            ->setLimit($limit);

        $paginated = $pagination->getData();
        $data = $serializer->serialize($paginated, 'json');

        $response->setJson($data);

        return $response;
    }

    /**
     * Mobile creation - Admin only
     * @Route("admin/mobiles", name="create", methods={"POST"})
     * @SWG\Parameter(
     *   name="Mobile",
     *   description="Fields to provide to create a mobile",
     *   in="body",
     *   required=true,
     *   type="string",
     *   @SWG\Schema(
     *     type="object",
     *     title="Mobile field",
     *     @SWG\Property(property="name", type="string"),
     *     @SWG\Property(property="price", type="string"),
     *     @SWG\Property(property="description", type="string")
     *     )
     * )
     * @SWG\Response(
     *     response=201,
     *     description="CREATED",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Mobile::class))
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
     * @SWG\Tag(name="Mobile")
     * @nSecurity(name="Bearer")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    public function create(Request $request, EntityManagerInterface $manager, SerializerInterface $serializer, ValidatorInterface $validator) : JsonResponse
    {
        $mobile = $serializer->deserialize($request->getContent(), Mobile::class, 'json');
        $errors = $validator->validate($mobile);
        if(count($errors) > 0) {
            $data = $serializer->serialize($errors, 'json');
            return new JsonResponse($data, 400, [], true);
        }
        $manager->persist($mobile);
        $manager->flush();
        $data = $serializer->serialize($mobile, 'json');
        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    /**
     * Mobile update - Admin only
     * @Route("admin/mobiles/{id}", name="update", methods={"PUT"})
     * @SWG\Parameter(
     *   name="id",
     *   description="Id of the mobile to update",
     *   in="path",
     *   required=true,
     *   type="integer"
     * )
     * @SWG\Parameter(
     *   name="Mobile",
     *   description="Fields to provide to update a mobile",
     *   in="body",
     *   required=true,
     *   type="string",
     *   @SWG\Schema(
     *     type="object",
     *     title="Mobile field",
     *     @SWG\Property(property="name", type="string"),
     *     @SWG\Property(property="price", type="string"),
     *     @SWG\Property(property="description", type="string")
     *     )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Mobile::class))
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
     * @SWG\Tag(name="Mobile")
     * @nSecurity(name="Bearer")
     * @param Mobile $mobile
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param FormErrors $formErrors
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function update(Mobile $mobile, Request $request, EntityManagerInterface $manager, FormErrors $formErrors, SerializerInterface $serializer) : JsonResponse
    {
        $data = json_decode($request->getContent(), 'json');
        // Use symfony/forms for update @see https://github.com/schmittjoh/JMSSerializerBundle/issues/575#issuecomment-303058694
        $form = $this->createForm(MobileType::class, $mobile);
        $form->submit($data);
        if($form->isSubmitted() && !$form->isValid()) {
            $errors = $formErrors->getErrors($form);
            return new JsonResponse($errors, 400, [], false);
        }
        $manager->flush();
        $data = $serializer->serialize($mobile, 'json');
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * Mobile deletion - Admin only
     * @Route("admin/mobiles/{id}", name="delete", methods={"DELETE"})
     * @SWG\Parameter(
     *   name="id",
     *   description="Id of the mobile to delete",
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
     * @SWG\Tag(name="Mobile")
     * @nSecurity(name="Bearer")
     * @param Mobile $mobile
     * @param EntityManagerInterface $manager
     * @return JsonResponse
     */
    public function delete(Mobile $mobile, EntityManagerInterface $manager) : JsonResponse
    {
        $manager->remove($mobile);
        $manager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}

