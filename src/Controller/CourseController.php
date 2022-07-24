<?php

namespace App\Controller;

use App\DTO\CourseDTO;
use App\DTO\Request\CourseRequest;
use App\DTO\Response\CourseResponse;
use App\Repository\CourseRepository;
use App\Repository\UserRepository;
use App\Service\PaymentService;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('api/v1')]
class CourseController extends AbstractController
{
    public CourseResponse $courseResponse;
    protected $serializer;
    public PaymentService $paymentService;
    public CourseRequest $courseRequest;
    private $validator;

    public function __construct(CourseResponse $courseResponse,
                                PaymentService $paymentService,
                                ValidatorInterface $validator,
                                CourseRequest $courseRequest)
    {
        $this->serializer = SerializerBuilder::create()->build();
        $this->courseResponse = $courseResponse;
        $this->paymentService = $paymentService;
        $this->courseRequest = $courseRequest;
        $this->validator = $validator;
    }

    #[Route('/courses', name: 'app_list_course', methods: ['GET'])]
    public function list(CourseRepository $courseRepository): Response
    {
        return $this->json($this->courseResponse->transformFromObjects($courseRepository->findAll()), Response::HTTP_OK);
    }

    #[Route('/courses/{code}', name: 'app_find_course', methods: ['GET'])]
    public function find($code, CourseRepository $courseRepository): Response
    {
        $course = $courseRepository->findOneBy(['characterCode' => $code]);
        if ($course){
            return $this->json($this->courseResponse->transformFromObject($course), Response::HTTP_OK);
        }
        return $this->json(['code' => 406 , 'message' => 'Курс не найден'], Response::HTTP_NOT_ACCEPTABLE);
    }

    #[Route('/courses/{code}/pay', name: 'app_pay_course', methods: ['POST'])]
    public function pay($code, CourseRepository $courseRepository, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $course = $courseRepository->findOneBy(['characterCode' => $code]);
        if ($course){
            $user = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
            if ($this->paymentService->payment($course, $user, $entityManager)){
                if ($course->getType() === 0) {
                    $answer['course_type'] = '0';
                } elseif ($course->getType() === 1) {
                    $answer['course_type'] = '2';
                    $answer['expires_at'] = (new \DateTime())->modify('+1 week');
                } else {
                    $answer['course_type'] = '1';
                }
                $answer = ['success' => true];
                return $this->json($answer, Response::HTTP_OK);
            } else {
                return $this->json(['code' => 406 , 'message' => 'Недостаточно средств на вашем счету'], Response::HTTP_NOT_ACCEPTABLE);
            }
        }
        return $this->json(['code' => 406 , 'message' => 'Курс не найден'], Response::HTTP_NOT_ACCEPTABLE);
    }

    #[Route('/courses', name: 'app_new_course', methods: ['POST'])]
    public function new(CourseRepository $courseRepository, UserRepository $userRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        try {
            $courseDTO = $this->serializer->deserialize($request->getContent(), CourseDTO::class, 'json');
            $course = $this->courseRequest->transformToObject($courseDTO);
            $entityManager->persist($course);
            $entityManager->flush();
            return $this->json(['success'=> true], Response::HTTP_CREATED);
        }catch (\Exception $exception){
            return $this->json(['errors' => $exception], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/courses/{code}', name: 'app_change_course', methods: ['POST'])]
    public function change($code, CourseRepository $courseRepository, UserRepository $userRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $course = $courseRepository->findOneBy(['characterCode' => $code]);
        if ($course){
            try {
                $courseDTO = $this->serializer->deserialize($request->getContent(), CourseDTO::class, 'json');
                $course = $this->courseRequest->transformToObject($courseDTO);
                $entityManager->persist($course);
                $entityManager->flush();
                return $this->json(['success'=> true], Response::HTTP_CREATED);
            } catch (\Exception $exception){
                return $this->json(['success' => false], Response::HTTP_BAD_REQUEST);
            }
        }
        return $this->json(['success' => false], Response::HTTP_BAD_REQUEST);
    }
}
