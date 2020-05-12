<?php

namespace App\Port\Controller;

use App\Domain\Analytic\Event\TrackAnalytic;
use App\Domain\SocialTechCustomer\Entity\AuthToken;
use App\Domain\SocialTechCustomer\Exception\CustomerNotFoundException;
use App\Domain\SocialTechCustomer\Service\AuthService;
use App\Domain\SocialTechCustomer\ValueObject\NickName;
use App\Validation\ValueObjectAssertion;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;

/**
 * @Route("/api/v1/auth", name="api_v1_tracking")
 */
class AuthController implements RestControllerInterface
{
    /** @var AuthService */
    private AuthService $authService;

    /** @var EventDispatcherInterface */
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(AuthService $authService, EventDispatcherInterface $eventDispatcher)
    {
        $this->authService = $authService;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route("/login", name="login", methods={"POST"})
     * @SWG\Post(
     *      security={
     *          {"api_key":{}}
     *      },
     *      summary="Customer login",
     *      tags={"auth"},
     *      consumes={"application/json"},
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="JSON Payload",
     *          required=true,
     *          type="json",
     *          format="application/json",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="login_data", type="object",
     *                  @SWG\Property(property="nickname", type="string", example="stillartem"),
     *                  @SWG\Property(property="password", type="string", example="test")
     *          ),
     *              @SWG\Property(property="analytic_data", type="object",
     *                  @SWG\Property(property="source_label", type="string", example="login_page"),
     *                  @SWG\Property(property="id", type="string", example="1"),
     *                  @SWG\Property(property="date_created", type="string", example="2018-04-10 12:09:07"),
     *          )
     * )
     *
     *      ),
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns the auth token of an user"
     * )
     * @param Request $request
     *
     * @return JsonResponse
     * @throws CustomerNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Exception
     */
    public function login(Request $request): JsonResponse
    {
        $body = json_decode($request->getContent(), true) ?? [];
        $this->validateRequestDataForLogin($body);
        $this->validateAnalyticDate($body);

        $loginData = $body['login_data'];
        $analyticData = $body['analytic_data'];

        $nickName = NickName::fromScalar($loginData['nickname']);
        $authToken = $this->authService->login($nickName, $loginData['password']);

        $analyticEvent = $this->getAnalyticEvent(
            $authToken,
            $analyticData['date_created'],
            $analyticData['id'],
            $analyticData['source_label']
        );
        $this->eventDispatcher->dispatch($analyticEvent);

        return new JsonResponse($authToken->asArray());
    }

    /**
     * @Route("/registration", name="registration", methods={"POST"})
     * @SWG\Post(
     *      security={
     *          {"api_key":{}}
     *      },
     *      summary="Customer registration",
     *      tags={"auth"},
     *      consumes={"application/json"},
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="JSON Payload",
     *          required=true,
     *          type="json",
     *          format="application/json",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="user_data", type="object",
     *                  @SWG\Property(property="first_name", type="string", example="Artem"),
     *                  @SWG\Property(property="last_name", type="string", example="Still"),
     *                  @SWG\Property(property="nickname", type="string", example="stillartem"),
     *                  @SWG\Property(property="age", type="int", example="25"),
     *                  @SWG\Property(property="password", type="string", example="test"),
     *          ),
     *              @SWG\Property(property="analytic_data", type="object",
     *                  @SWG\Property(property="source_label", type="string", example="registration_page"),
     *                  @SWG\Property(property="id", type="string", example="1"),
     *                  @SWG\Property(property="date_created", type="string", example="2018-04-10 12:09:07"),
     *          )
     * )
     *
     *      ),
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns the auth token of an user"
     * )
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function registration(Request $request): JsonResponse
    {
        $body = json_decode($request->getContent(), true) ?? [];
        $this->validateAnalyticDate($body);

        $userData = $body['user_data'] ?? [];
        $authToken = $this->authService->register($userData);

        $analyticData = $body['analytic_data'];
        $analyticEvent = $this->getAnalyticEvent(
            $authToken,
            $analyticData['date_created'],
            $analyticData['id'],
            $analyticData['source_label']
        );
        $this->eventDispatcher->dispatch($analyticEvent);

        return new JsonResponse($authToken->asArray());
    }

    /**
     * @param array $body
     */
    private function validateAnalyticDate(array $body): void
    {
        ValueObjectAssertion::ensure(!empty($body['analytic_data']['id']), 'analytic_data.id');
        ValueObjectAssertion::ensure(!empty($body['analytic_data']['source_label']), 'analytic_data.source_label');
        ValueObjectAssertion::ensure(!empty($body['analytic_data']['date_created']), 'analytic_data.date_created');
    }

    /**
     * @param array $body
     */
    private function validateRequestDataForLogin(array $body): void
    {
        ValueObjectAssertion::ensure(!empty($body['login_data']['nickname']), 'login_data.nickname');
        ValueObjectAssertion::ensure(!empty($body['login_data']['password']), 'login_data.password');
    }

    /**
     * @param AuthToken $authToken
     * @param string $createdAt
     * @param string $trackingId
     * @param string $source
     *
     * @return TrackAnalytic
     * @throws \Exception
     */
    private function getAnalyticEvent(
        AuthToken $authToken,
        string $createdAt,
        string $trackingId,
        string $source
    ): TrackAnalytic {

        return TrackAnalytic::fromScalar($authToken->getUserId(), $source, $trackingId, $createdAt);
    }
}
