<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;


class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="login", methods={"POST"})
     * @SWG\Parameter(
     *   name="body",
     *   in="body",
     *   required=true,
     *   @SWG\Schema(
     *     type="object",
     *     title="Login field",
     *     @SWG\Property(property="email", type="string"),
     *     @SWG\Property(property="password", type="string")
     *     )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return authentication token"
     * )
     * @SWG\Tag(name="Authentication")
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
