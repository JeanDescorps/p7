<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Service\FormErrors;
use App\Service\Pagination;
use App\Service\TableDetails;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ClientController
 * @package App\Controller
 * @Route("api/", name="client_")
 */
class ClientController extends AbstractController
{
    /**
     * Get details about a specific client
     * @Route("clients/{id}", name="show", methods={"GET"})
     * @SWG\Parameter(
     *   name="id",
     *   description="Id of the client to get",
     *   in="path",
     *   required=true,
     *   type="integer"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Client::class))
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
     * @Route("admin/clients", name="list", methods={"GET"})
     * @SWG\Parameter(
     *   name="page",
     *   description="The page number to show",
     *   in="query",
     *   type="integer"
     * )
     * @SWG\Parameter(
     *   name="limit",
     *   description="The number of client per page",
     *   in="query",
     *   type="integer"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *      @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Client::class))
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
     * @SWG\Tag(name="Client")
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
        $response->setEtag(md5($tableDetails->lastUpdate('client') . $this->getUser()->getEmail()));
        $response->headers->addCacheControlDirective('no-control');
        $response->setPublic();

        if($response->isNotModified($request)) {
            return $response;
        }

        $limit = $request->query->get('limit', $this->getParameter('default_client_limit'));
        $page = $request->query->get('page', 1);
        $route = $request->attributes->get('_route');

        $pagination->setEntityClass(Client::class)
            ->setRoute($route);
        $pagination->setCurrentPage($page)
            ->setLimit($limit);

        $paginated = $pagination->getData();
        $data = $serializer->serialize($paginated, 'json');

        $response->setJson($data);

        return $response;
    }

    /**
     * Client creation - Admin only
     * @Route("admin/clients", name="create", methods={"POST"})
     * @SWG\Parameter(
     *   name="Client",
     *   description="Fields to provide to create a client",
     *   in="body",
     *   required=true,
     *   type="string",
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
     *     description="CREATED",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Client::class))
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
     * @SWG\Tag(name="Client")
     * @nSecurity(name="Bearer")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoderInterface $encoder
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    public function create(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, SerializerInterface $serializer, ValidatorInterface $validator) : JsonResponse
    {
        $client = $serializer->deserialize($request->getContent(), Client::class, 'json');
        $errors = $validator->validate($client);
        if(count($errors) > 0) {
            $data = $serializer->serialize($errors, 'json');
            return new JsonResponse($data, 400, [], true);
        }
        $plainPassword = $client->getPassword();
        $password = $encoder->encodePassword($client, $client->getPassword());
        $client->setPassword($password);
        $manager->persist($client);
        $manager->flush();

        $subject = 'Account creation';
        $content = $this->renderView('emails/creation.html.twig', [
                'name' => $client->getName(),
                'email' => $client->getEmail(),
                'password' => $plainPassword,
            ]
        );
        $headers = 'From: "Bilemo"<webdev@jeandescorps.fr>' . "\n";
        $headers .= 'Reply-To: jean.webdev@gmail.com' . "\n";
        $headers .= 'Content-Type: text/html; charset="iso-8859-1"' . "\n";
        $headers .= 'Content-Transfer-Encoding: 8bit';
        mail($client->getEmail(), $subject, $content, $headers);

        $data = $serializer->serialize($client, 'json');
        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    /**
     * Client update
     * @Route("clients/{id}", name="update", methods={"PUT"})
     * @SWG\Parameter(
     *   name="id",
     *   description="Id of the client to update",
     *   in="path",
     *   required=true,
     *   type="integer"
     * )
     * @SWG\Parameter(
     *   name="Client",
     *   description="Fields to provide to update a client",
     *   in="body",
     *   required=true,
     *   type="string",
     *   @SWG\Schema(
     *     type="object",
     *     title="Client field",
     *     @SWG\Property(property="name", type="string"),
     *     @SWG\Property(property="email", type="string"),
     *     @SWG\Property(property="password", type="string")
     *     )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=CLient::class))
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
     * @SWG\Tag(name="Client")
     * @nSecurity(name="Bearer")
     * @Security("user === client || is_granted('ROLE_ADMIN')")
     * @param Client $client
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param FormErrors $formErrors
     * @param UserPasswordEncoderInterface $encoder;
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function update(Client $client, Request $request, EntityManagerInterface $manager, FormErrors $formErrors, UserPasswordEncoderInterface $encoder, SerializerInterface $serializer) : JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        // Use symfony/forms for update @see https://github.com/schmittjoh/JMSSerializerBundle/issues/575#issuecomment-303058694
        $form = $this->createForm(ClientType::class, $client);
        $form->submit($data);
        if($form->isSubmitted() && !$form->isValid()) {
            $errors = $formErrors->getErrors($form);
            return new JsonResponse($errors, 400, [], false);
        }
        $password = $encoder->encodePassword($client, $client->getPassword());
        $client->setPassword($password);
        $manager->flush();
        $data = $serializer->serialize($client, 'json');
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * Client delete - Admin only
     * @Route("admin/clients/{id}", name="delete", methods={"DELETE"})
     * @SWG\Parameter(
     *   name="id",
     *   description="Id of the client to delete",
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
     * @SWG\Tag(name="Client")
     * @nSecurity(name="Bearer")
     * @param Client $client
     * @param EntityManagerInterface $manager
     * @return JsonResponse
     */
    public function delete(Client $client, EntityManagerInterface $manager) : JsonResponse
    {
        $manager->remove($client);
        $manager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
