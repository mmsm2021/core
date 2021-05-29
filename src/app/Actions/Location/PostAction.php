<?php

namespace App\Actions\Location;

use App\Data\Types\Point;
use App\Data\Validator\LocationValidator;
use App\Database\Entities\Location;
use App\Database\Repositories\CountryRepository;
use App\Database\Repositories\LocationRepository;
use App\Exceptions\NoSuchEntityException;
use App\Exceptions\SaveException;
use MMSM\Lib\Authorizer;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SimpleJWT\JWT;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;
use Slim\Exception\HttpInternalServerErrorException;
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
     * @var Authorizer
     */
    private Authorizer $authorizer;

    /**
     * @var CountryRepository
     */
    private CountryRepository $countryRepository;

    /**
     * Get constructor.
     * @param JsonResponseFactory $jsonResponseFactory
     * @param LocationValidator $locationValidator
     * @param LocationRepository $locationRepository
     * @param Authorizer $authorizer
     * @param CountryRepository $countryRepository
     */
    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        LocationValidator $locationValidator,
        LocationRepository $locationRepository,
        Authorizer $authorizer,
        CountryRepository $countryRepository
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->locationValidator = $locationValidator;
        $this->locationRepository = $locationRepository;
        $this->authorizer = $authorizer;
        $this->countryRepository = $countryRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/locations",
     *     summary="Creates new location from carried JSON",
     *     tags={"Location"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer {id-token}",
     *         @OA\Schema(
     *              ref="#/components/schemas/jwt"
     *         )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Will reply with the created locations in JSON format",
     *         @OA\JsonContent(ref="#/components/schemas/Location")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="will contain a JSON object with a message.",
     *         @OA\JsonContent(ref="#/components/schemas/error")
     *     ),
     *     @OA\Response(
     *         response=403,
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
     * @return Response
     * @throws \Throwable
     */
    public function __invoke(Request $request): Response
    {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            throw new HttpBadRequestException(
                $request,
                'Invalid body.'
            );
        }

        $this->authorizer->authorizeToRole($request, 'user.roles.super');
        $this->locationValidator->postCheck($body);
        try {
            $location = new Location();
            $location->setName($body['name']);

            if (!$this->locationRepository->isNameUnique($location->getName())) {
                throw new HttpBadRequestException($request, 'name already exists.');
            }

            $location->setPoint(Point::fromArray($body['point']));
            $location->setMetadata($body['metadata']);
            $location->setStreet($body['street']);
            $location->setNumber($body['number']);
            $location->setZipcode((string)$body['zipcode']);
            $location->setCity($body['city']);
            if (isset($body['state'])) {
                $location->setState($body['state']);
            }
            if (is_array($body['country'])) {
                $body['country'] = $body['country']['iso3'];
            }
            try {
                $country = $this->countryRepository->getByIso3($body['country']);
            } catch (NoSuchEntityException $exception) {
                $country = $this->countryRepository->getByName($body['country']);
            }
            $location->setCountry($country);
            $this->locationRepository->save($location);
            return $this->jsonResponseFactory->create(200, $location->toArray());
        } catch (SaveException $exception) {
            throw new HttpInternalServerErrorException($request, $exception->getMessage(), $exception);
        } catch (NoSuchEntityException $noSuchEntityException) {
            throw new HttpBadRequestException($request, 'Invalid Country', $noSuchEntityException);
        }
    }
}