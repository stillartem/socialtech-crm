<?php

namespace App\Port\Controller;

use App\Domain\Analytic\Event\TrackAnalytic;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;

/**
 * @Route("/api/v1/tracking", name="api_v1_tracking")
 */
class TrackingController implements TokenAuthenticatedController, RestControllerInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route("", name="track", methods={"POST"})
     * @SWG\Post(
     *      security={
     *          {"api_key":{123}}
     *      },
     *      summary="Track customer actions",
     *      tags={"tracking"},
     *      consumes={"application/json"},
     *      produces={"application/json"},
     *     @SWG\Parameter(
     *          name="X-API-TOKEN",
     *          in="header",
     *          type="string",
     *          description="Api Token"
     *     ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="JSON Payload",
     *          required=true,
     *          type="json",
     *          format="application/json",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="source_label", type="string", example="search_page"),
     *              @SWG\Property(property="id", type="string", example="1"),
     *              @SWG\Property(property="date_created", type="string", example="2018-04-10 12:09:07"),
     *              @SWG\Property(property="id_user", type="string", example="f7a8ce76-101d-47bd-bfa3-884e00846091"),
     *          )
     *
     *      ),
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Returns nothing"
     * )
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function track(Request $request): JsonResponse
    {
        $body = json_decode($request->getContent(), true) ?? [];
        $analyticEvent = $this->getAnalyticEvent($body);
        $this->eventDispatcher->dispatch($analyticEvent);

        return new JsonResponse(null, 204);
    }

    /**
     * @param array $content
     *
     * @return TrackAnalytic
     * @throws \Exception
     */
    private function getAnalyticEvent(array $content): TrackAnalytic
    {
        $userId = $content['id_user'] ?? null;
        $sourceLabel = $content['source_label'] ?? null;
        $id = $content['id'] ?? null;
        $createdAt = $content['date_created'] ?? null;

        return TrackAnalytic::fromScalar($userId, $sourceLabel, $id, $createdAt);
    }
}
