<?php

namespace App\Actions\Location;

use App\Data\Types\Point;
use App\Data\Validator\LocationValidator;
use App\Database\Entities\Country;
use App\Database\Repositories\CountryRepository;
use App\Database\Repositories\LocationRepository;
use App\Exceptions\EntityNotFoundException;
use App\Exceptions\SaveException;
use MMSM\Lib\Authorizer;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpNotFoundException;

class PatchAction
{
    /**
     * @var LocationRepository
     */
    private LocationRepository $locationRepository;

    /**
     * @var Authorizer
     */
    private Authorizer $authorizer;

    /**
     * @var LocationValidator
     */
    private LocationValidator $locationValidator;

    /**
     * @var CountryRepository
     */
    private CountryRepository $countryRepository;

    /**
     * @var JsonResponseFactory
     */
    private JsonResponseFactory $jsonResponseFactory;

    /**
     * PatchAction constructor.
     * @param LocationRepository $locationRepository
     * @param Authorizer $authorizer
     * @param LocationValidator $locationValidator
     * @param CountryRepository $countryRepository
     * @param JsonResponseFactory $jsonResponseFactory
     */
    public function __construct(
        LocationRepository $locationRepository,
        Authorizer $authorizer,
        LocationValidator $locationValidator,
        CountryRepository $countryRepository,
        JsonResponseFactory $jsonResponseFactory
    ) {
        $this->locationRepository = $locationRepository;
        $this->authorizer = $authorizer;
        $this->locationValidator = $locationValidator;
        $this->countryRepository = $countryRepository;
        $this->jsonResponseFactory = $jsonResponseFactory;
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/locations/{id}",
     *     summary="Updates location from carried JSON",
     *     tags={"Location"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The id of the location.",
     *         required=true,
     *         @OA\Schema(
     *             ref="#/components/schemas/uuid"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="The Location that you want to create.",
     *         @OA\JsonContent(ref="#/components/schemas/UpdateLocationDTO"),
     *     ),
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
     *         response=401,
     *         description="will contain a JSON object with a message.",
     *         @OA\JsonContent(ref="#/components/schemas/error")
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
     * @throws HttpBadRequestException
     * @throws HttpInternalServerErrorException
     * @throws HttpNotFoundException
     */
    public function __invoke(Request $request, string $id): Response
    {
        try {
            $body = $request->getParsedBody();
            if (!is_array($body)) {
                throw new HttpBadRequestException(
                    $request,
                    'Invalid Body.'
                );
            }
            $location = $this->locationRepository->getById(
                $id,
                $this->authorizer->hasRole($request, 'user.roles.super', false)
            );
            $this->locationValidator->patchCheck($body);
            foreach ($body as $key => $value) {
                switch ($key) {
                    case 'name':
                        $location->setName($value);
                        break;
                    case 'point':
                        $location->setPoint(Point::fromArray($value));
                        break;
                    case 'metadata':
                        $location->setMetadata($value);
                        break;
                    case 'street':
                        $location->setStreet($value);
                        break;
                    case 'number':
                        $location->setNumber($value);
                        break;
                    case 'zipcode':
                        $location->setZipcode((string)$value);
                        break;
                    case 'city':
                        $location->setCity($value);
                        break;
                    case 'state':
                        $location->setState($value);
                        break;
                    case 'country':
                        $iso3 = $value;
                        if (is_array($value)) {
                            $iso3 = $value['iso3'];
                        }
                        try {
                            $country = $this->countryRepository->getByIso3($iso3);
                        } catch (EntityNotFoundException $entityNotFoundException) {
                            try {
                                $country = $this->countryRepository->getByName($iso3);
                            } catch (EntityNotFoundException $entityNotFoundException) {
                                $country = false;
                            }
                        }
                        if (!($country instanceof Country)) {
                            throw new HttpBadRequestException(
                                $request,
                                'Failed to find country.'
                            );
                        }
                        $location->setCountry($country);
                        break;
                }
            }
            $this->locationRepository->save($location);
            return $this->jsonResponseFactory->create(200, $location->toArray());
        } catch (EntityNotFoundException $entityNotFoundException) {
            throw new HttpNotFoundException(
                $request,
                'Failed to find location by id "' . $id . '".',
                $entityNotFoundException
            );
        } catch (SaveException $saveException) {
            throw new HttpInternalServerErrorException(
                $request,
                'Failed to save location.',
                $saveException
            );
        }
    }
}