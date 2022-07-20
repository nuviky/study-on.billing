<?php

namespace App\Controller;

use App\DTO\Request\UserRegisterRequest;
use App\DTO\UserDTO;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerBuilder;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Annotations as OA;

#[Route('api/v1')]
class AuthUserController extends AbstractController
{

    private $serializer;
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->serializer = SerializerBuilder::create()->build();
        $this->validator = $validator;
    }

    /**
     * @OA\Post(
     *     path="api/v1/auth",
     *     description="Authenticate user with JWT token",
     * )
     * @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *          @OA\Property(
     *              property="username",
     *              type="string",
     *              example="user@example.com"
     *          ),
     *          @OA\Property(
     *              property="password",
     *              type="string",
     *              example="123qwe"
     *          )
     *       )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns JWT token for user authentication",
     *     @OA\JsonContent(
     *        @OA\Property(
     *          property="token",
     *          type="string"
     *        )
     *     )
     * )
     * @OA\Response(
     *     response=401,
     *     description="User authentication failed",
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
    #[Route('/auth', name: 'api_login', methods: ['POST'])]
    public function login(): Response
    {
        //auth
    }

    /**
     * @OA\Post(
     *     path="api/v1/register",
     *     description="Register user",
     * )
     * @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *          @OA\Property(
     *              property="username",
     *              type="string",
     *              example="user@example.com"
     *          ),
     *          @OA\Property(
     *              property="password",
     *              type="string",
     *              example="123qwe"
     *          )
     *       )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Returns JWT token and roles for user authentication",
     *     @OA\JsonContent(
     *        @OA\Property(
     *          property="token",
     *          type="string"
     *        ),
     *        @OA\Property(
     *          property="roles",
     *          type="array",
     *          @OA\Items(type="string")
     *        )
     *     )
     * )
     * @OA\Response(
     *     response=400,
     *     description="User registration failed",
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
    #[Route('/register', name: 'api_registration', methods: ['POST'])]
    public function registration(
        Request $request,
        UserRegisterRequest $registerDTO,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $tokenManager
    ): Response
    {
        $userDto = $this->serializer->deserialize(
            $request->getContent(),
            UserDTO::class,
            'json'
        );

        $errors = $this->validator->validate($userDto);
        if ($userRepository->findOneBy(['email' => $userDto->username])) {
            $errors->add(new ConstraintViolation(
                message: 'User ' . $userDto->username .  ' already exists.',
                messageTemplate: 'User {{ value }} already exists.',
                parameters: ['value' => $userDto->username],
                root: $userDto,
                propertyPath: 'username',
                invalidValue: $userDto->username
            ));
        }
        if (count($errors) > 0) {
            $jsonErrors = [];
            foreach ($errors as $error) {
                $jsonErrors[$error->getPropertyPath()][] = $error->getMessage();
            }
            return $this->json([
                'errors' => $jsonErrors,
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = $registerDTO->transformToObject($userDto);
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
                'token' => $tokenManager->create($user),
                'roles' => $user->getRoles()
        ],
            Response::HTTP_CREATED
        );
    }
}
