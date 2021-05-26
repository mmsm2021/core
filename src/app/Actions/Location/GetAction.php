<?php

namespace App\Actions\Location;

use App\Database\Repositories\LocationRepository;
use App\Exceptions\NoSuchEntityException;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

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
     * Get constructor.
     * @param JsonResponseFactory $jsonResponseFactory
     * @param LocationRepository $locationRepository
     */
    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        LocationRepository $locationRepository
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->locationRepository = $locationRepository;
    }

    /**
     * @param Request $request
     * @param string $id
     * @return Response
     */
    public function __invoke(Request $request, string $id): Response
    {
        try {
            $location = $this->locationRepository->getById($id, false);
            return $this->jsonResponseFactory->create(200, $location->toArray());
        } catch (NoSuchEntityException $exception) {
            return $this->jsonResponseFactory->create(400, [
                'error' => true,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}