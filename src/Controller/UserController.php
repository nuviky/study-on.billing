<?php

namespace App\Controller;

use App\DTO\Response\CurrentUserResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use OpenApi\Annotations as OA;

#[Route('api/v1')]
class UserController extends AbstractController
{

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @OA\Post(
     *     path="api/v1/users/current",
     *     description="Get user current",
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns current user",
     *     @OA\JsonContent(
     *        @OA\Property(
     *          property="username",
     *          type="string"
     *        ),
     *        @OA\Property(
     *          property="balance",
     *          type="float"
     *        ),
     *        @OA\Property(
     *          property="roles",
     *          type="array",
     *          @OA\Items(type="string")
     *        )
     *     )
     * )
     * @OA\Response(
     *     response=401,
     *     description="User not authenticated",
     *     @OA\JsonContent(
     *        @OA\Property(
     *          property="code",
     *          type="string"
     *        ),
     *        @OA\Property(
     *          property="message",
     *          type="string"
     *        )
     *     )
     * )
     * @OA\Response(
     *     response="default",
     *     description="Unxepected error",
     *     @OA\JsonContent(
     *        @OA\Property(
     *          property="code",
     *          type="string"
     *        ),
     *        @OA\Property(
     *          property="message",
     *          type="string"
     *        ),
     *     )
     * )
     */
    #[Route('/users/current', name: 'app_current_user', methods: ['GET'])]
    public function current(): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return $this->json([
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'message' => 'User not authenticated.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $userDto = (new CurrentUserResponse())->transformFromObject($user);
        return $this->json(
            $userDto,
            Response::HTTP_OK
        );
    }
}
