<?php

namespace App\Controller;

use App\DTO\Response\TransactionResponse;
use App\Repository\CourseRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

#[Route('/api/v1/transactions')]
class TransactionController extends AbstractController
{
    public TransactionResponse $transactionResponse;

    public function __construct(TransactionResponse $transactionResponse)
    {
        $this->transactionResponse = $transactionResponse;
    }
    /**
     * @OA\Get(
     *     tags={"Transactions"},
     *     path="/api/v1/transactions/",
     *     description="История начислений и списаний текущего пользователя",
     *     summary="История начислений и списаний текущего пользователя",
     *     security={
     *         { "Bearer":{} },
     *     },
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Тип транзакции [payment | deposit]",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="course_code",
     *         in="query",
     *         description="Символьный код курса",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="skip_expired",
     *         in="query",
     *         description="Отбросить записи с датой expires_at оплаты аренд, которые уже истекли",
     *         @OA\Schema(type="bool")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список транзакций",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(
     *                         property="id",
     *                         type="int"
     *                     ),
     *                     @OA\Property(
     *                         property="created_at",
     *                         type="string"
     *                     ),
     *                     @OA\Property(
     *                         property="type",
     *                         type="string"
     *                     ),
     *                     @OA\Property(
     *                         property="course_code",
     *                         type="string"
     *                     ),
     *                     @OA\Property(
     *                         property="amount",
     *                         type="number"
     *                     ),
     *                      @OA\Property(
     *                         property="expires_at",
     *                         type="string"
     *                     ),
     *                 )
     *             ),
     *        )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неверный JWT Token",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="code",
     *                     type="string",
     *                     example="401",
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Неверный JWT Token",
     *                 ),
     *             ),
     *        )
     *     )
     * )
     */
    #[Route('', name: 'app_transactions')]
    public function index(Request $request,UserRepository $userRepository, CourseRepository $courseRepository, TransactionRepository $transactionRepository): Response
    {
        $filters = $request->query->all()["filter"] ?? null;
        if ($filters) {
            $query = $transactionRepository
                ->createQueryBuilder('t')
                ->andWhere('t.user_ = :user')
                ->setParameter('user', $userRepository->findOneBy(['email'=>$this->getUser()->getUserIdentifier()]))
                ->orderBy('t.date', 'DESC');
            if (isset($filters["type"])) {
                $query->andWhere('t.type = :type')
                    ->setParameter('type', $filters["type"] === "payment" ? 0 :1);
            }
            if (isset($filters["course_code"])) {
                $course = $courseRepository->findOneBy(['characterCode' => $filters["course_code"]]);
                $query->andWhere('t.course = :course')
                    ->setParameter('course', $course?->getId());
            }
            if (isset($filters["skip_expired"])) {
                $date = new \DateTime();
                $query->andWhere('t.validityPeriod is null');
                $query->orWhere('t.validityPeriod >= :today')
                    ->setParameter('today', $date);
            }
            return $this->json($this->transactionResponse->transformFromObjects($query->getQuery()->getResult()));
        }
        return $this->json($this->transactionResponse->transformFromObjects($transactionRepository->findAll()));
    }
}