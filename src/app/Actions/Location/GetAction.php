<?php

namespace App\Actions\Location;

use App\Database\Repositories\LocationRepository;
use App\Exceptions\NoSuchEntityException;
use MMSM\Lib\Authorizer;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
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
        } catch (NoSuchEntityException $exception) {
            return $this->jsonResponseFactory->create(400, [
                'error' => true,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}