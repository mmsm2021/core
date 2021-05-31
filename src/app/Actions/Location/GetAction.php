<?php

namespace App\Actions\Location;

use App\Database\Repositories\LocationRepository;
use App\Exceptions\EntityNotFoundException;
use MMSM\Lib\Authorizer;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;
use Throwable;

class GetAction
{
    /**
     * @var JsonResponseFactory
     */
    private JsonResponseFactory $jsonResponseFactory;

    /**
     * @var LocationRepository
     */
    private LocationRepository $locationRepository;

    /**
     * @var Authorizer
     */
    private Authorizer $authorizer;

    /**
     * Get constructor.
     * @param JsonResponseFactory $jsonResponseFactory
     * @param LocationRepository $locationRepository
     * @param Authorizer $authorizer
     */
    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        LocationRepository $locationRepository,
        Authorizer $authorizer
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->locationRepository = $locationRepository;
        $this->authorizer = $authorizer;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/locations/{id}",
     *     summary="Returns a JSON object of a location",
     *     tags={"Location"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Bearer {id-token}",
     *         required=false,
     *         @OA\Schema(
     *             ref="#/components/schemas/jwt"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The id of the location.",
     *         required=true,
     *         @OA\Schema(
     *             ref="#/components/schemas/uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Will reply with the location in JSON format",
     *         @OA\JsonContent(ref="#/components/schemas/Location")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="will contain a JSON object with a message.",
     *         @OA\JsonContent(ref="#/components/schemas/error")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="will contain a JSON object with a message.",
     *         @OA\JsonContent(ref="#/components/schemas/error")
     *     )
     * )
     * @param Request $request
     * @param string $id
     * @return Response
     * @throws Throwable
     */
    public function __invoke(Request $request, string $id): Response
    {
        try {
            $location = $this->locationRepository->getById(
                $id,
                $this->authorizer->hasRole($request, 'user.roles.super', false)
            );
            return $this->jsonResponseFactory->create(200, $location->toArray());
        } catch (EntityNotFoundException $exception) {
            throw new HttpNotFoundException(
                $request,
                $exception->getMessage(),
                $exception
            );
        }
    }
}