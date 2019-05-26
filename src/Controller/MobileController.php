<?php

namespace App\Controller;

use App\Form\MobileType;
use App\Repository\MobileRepository;
use App\Service\FormErrors;
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

class MobileController extends AbstractController
{
    /**
     * Showing mobile
     * @Route("api/mobiles/{id}", name="mobile_show", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Return json array with mobile's details",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Mobile::class))
     *     )
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
     * Listing mobile
     * @Route("api/mobiles", name="mobile_list", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Return json array with all the mobiles"
     * )
     * @SWG\Tag(name="Mobile")
     * @nSecurity(name="Bearer")
     * @param MobileRepository $mobileRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function list(MobileRepository $mobileRepository, SerializerInterface $serializer) : JsonResponse
    {
        $mobiles = $mobileRepository->findAll();
        $data = $serializer->serialize($mobiles, 'json');
        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
    }

    /**
     * Mobile creation - Admin only
     * @Route("api/admin/mobiles", name="mobile_create", methods={"POST"})
     * @SWG\Parameter(
     *   name="body",
     *   in="body",
     *   required=true,
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
     *     description="Create a mobile"
     * )
     * @SWG\Tag(name="Mobile")
     * @nSecurity(name="Bearer")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param FormErrors $formErrors
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $manager, FormErrors $formErrors) : Response
    {
        $data = json_decode($request->getContent(), true);
        $mobile = new Mobile();
        $form = $this->createForm(MobileType::class, $mobile);
        $form->submit($data);
        if($form->isSubmitted() && !$form->isValid()) {
            $errors = $formErrors->getErrors($form);
            return new JsonResponse($errors, 400, [], false);
        }
        $manager->persist($mobile);
        $manager->flush();
        return new Response('', Response::HTTP_CREATED);
    }

    /**
     * Mobile update - Admin only
     * @Route("api/admin/mobiles/{id}", name="mobile_update", methods={"PUT"})
     * @SWG\Parameter(
     *   name="body",
     *   in="body",
     *   required=true,
     *   @SWG\Schema(
     *     type="object",
     *     title="Mobile field",
     *     @SWG\Property(property="name", type="string"),
     *     @SWG\Property(property="price", type="string"),
     *     @SWG\Property(property="description", type="string")
     *     )
     * )
     * @SWG\Response(
     *     response=202,
     *     description="Update a mobile"
     * )
     * @SWG\Tag(name="Mobile")
     * @nSecurity(name="Bearer")
     * @param Mobile $mobile
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param FormErrors $formErrors
     * @return Response
     */
    public function update(Mobile $mobile, Request $request, EntityManagerInterface $manager, FormErrors $formErrors) : Response
    {
        $data = json_decode($request->getContent(), 'json');
        $form = $this->createForm(MobileType::class, $mobile);
        $form->submit($data);
        if($form->isSubmitted() && !$form->isValid()) {
            $errors = $formErrors->getErrors($form);
            return new JsonResponse($errors, 400, [], false);
        }
        $manager->flush();
        return new Response('', Response::HTTP_ACCEPTED);
    }

    /**
     * Mobile delete - Admin only
     * @Route("api/admin/mobiles/{id}", name="mobile_delete", methods={"DELETE"})
     * @SWG\Response(
     *     response=202,
     *     description="Delete a mobile"
     * )
     * @SWG\Tag(name="Mobile")
     * @nSecurity(name="Bearer")
     * @param Mobile $mobile
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function delete(Mobile $mobile, EntityManagerInterface $manager) : Response
    {
        $manager->remove($mobile);
        $manager->flush();
        return new Response('', Response::HTTP_ACCEPTED);
    }

}

