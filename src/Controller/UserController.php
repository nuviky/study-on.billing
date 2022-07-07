<?php

namespace App\Controller;

use App\DTO\Response\CurrentUserResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

#[Route('api/v1')]
class UserController extends AbstractController
{

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

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
