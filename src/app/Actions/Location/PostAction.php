<?php

namespace App\Actions\Location;

use App\Data\Types\Point;
use App\Data\Validator\LocationValidator;
use App\Database\Entities\Location;
use App\Database\Repositories\LocationRepository;
use App\Exceptions\SaveException;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SimpleJWT\JWT;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;
use Slim\Exception\HttpUnauthorizedException;

class PostAction
{
    /**
     * @var JsonResponseFactory
     */
    private JsonResponseFactory $jsonResponseFactory;

    /**
     * @var LocationValidator
     */
    private LocationValidator $locationValidator;

    /**
     * @var LocationRepository
     */
    private LocationRepository $locationRepository;

    /**
     * Get constructor.
     * @param JsonResponseFactory $jsonResponseFactory
     * @param LocationValidator $locationValidator
     */
    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        LocationValidator $locationValidator,
        LocationRepository $locationRepository
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->locationValidator = $locationValidator;
        $this->locationRepository = $locationRepository;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Throwable
     */
    public function __invoke(Request $request): Response
    {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            return $this->jsonResponseFactory->create(400, [
                'error' => true,
                'message' => 'Invalid Body.',
            ]);
        }

        $token = $request->getAttribute('token');
        if ($token instanceof \Throwable) {
            throw $token;
        }
        if ($token === null || !($token instanceof JWT)) {
            throw new HttpUnauthorizedException($request, 'Missing or Invalid Token.');
        }

        $this->locationValidator->check($body);

        try {
            $location = new Location();
            $location->setName($body['name']);

            if (!$this->locationRepository->isNameUnique($location->getName())) {
                throw new HttpBadRequestException($request, 'name already exists.');
            }

            $location->setPoint(Point::fromArray($body['point']));
            $location->setMetadata($body['metadata']);
            $this->locationRepository->save($location);
            return $this->jsonResponseFactory->create(200, $location->toArray());
        } catch (SaveException $exception) {
            dd($exception);
            throw new HttpBadRequestException($request, $exception->getMessage(), $exception);
        }
    }
}