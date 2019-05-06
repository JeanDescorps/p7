<?php

namespace App\Controller;

use App\Form\MobileType;
use App\Repository\MobileRepository;
use App\Service\FormErrors;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\Mobile;

class MobileController extends AbstractController
{
    /**
     * Showing mobile
     * @Route("/mobiles/{id}", name="mobile_show", methods={"GET"})
     * @param Mobile $mobile
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function show(Mobile $mobile, SerializerInterface $serializer) : JsonResponse
    {
        $data = $serializer->serialize($mobile, 'json', ['groups' => 'mobile']);
        return new JsonResponse($data);
    }

    /**
     * Listing mobile
     * @Route("/mobiles", name="mobile_list", methods={"GET"})
     * @param MobileRepository $mobileRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function list(MobileRepository $mobileRepository, SerializerInterface $serializer) : JsonResponse
    {
        $mobiles = $mobileRepository->findAll();
        $data = $serializer->serialize($mobiles, 'json', ['groups' => 'mobile']);
        return new JsonResponse($data);
    }

    /**
     * Mobile creation
     * @Route("/mobiles", name="mobile_create", methods={"POST"})
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SerializerInterface $serializer
     * @param FormErrors $formErrors
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $manager, SerializerInterface $serializer, FormErrors $formErrors) : Response
    {
        $data = $serializer->decode($request->getContent(), 'json');
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
     * Mobile update
     * @Route("/mobiles/{id}", name="mobile_update", methods={"PUT"})
     * @param Mobile $mobile
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function update(Mobile $mobile, Request $request, EntityManagerInterface $manager, SerializerInterface $serializer) : Response
    {
        $data = $serializer->decode($request->getContent(), 'json');
        $form = $this->createForm(MobileType::class, $mobile);
        $form->submit($data);
        if($form->isSubmitted() && !$form->isValid()) {
            $data = (string)$form->getErrors(true,false);
            return new JsonResponse($data, 400);
        }
        $manager->flush();
        return new Response('', Response::HTTP_CREATED);
    }

    /**
     * Mobile delete
     * @Route("/mobiles/{id}", name="mobile_delete", methods={"DELETE"})
     * @param Mobile $mobile
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function delete(Mobile $mobile, EntityManagerInterface $manager) : Response
    {
        $manager->remove($mobile);
        $manager->flush();
        return new Response('', Response::HTTP_CREATED);
    }

}

