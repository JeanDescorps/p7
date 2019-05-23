<?php

namespace App\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;


class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="login", methods={"POST"})
     * @return JsonResponse
     */
    public function login() : JsonResponse
    {
        $user = $this->getUser();

        return $this->json([
            'name' => $user->getName(),
            'email' => $user->getEmail(),
        ]);
    }
}
