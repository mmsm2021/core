<?php

namespace App\Actions\Location;

use App\Data\Types\Point;
use App\Data\Validator\LocationValidator;
use App\Database\Entities\Country;
use App\Database\Repositories\CountryRepository;
use App\Database\Repositories\LocationRepository;
use App\Exceptions\NoSuchEntityException;
use App\Exceptions\SaveException;
use MMSM\Lib\Authorizer;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;

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
     * @param Request $request
     * @param string $id
     * @return Response
     * @throws HttpBadRequestException
     * @throws HttpInternalServerErrorException
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
                        } catch (NoSuchEntityException $noSuchEntityException) {
                            try {
                                $country = $this->countryRepository->getByName($iso3);
                            } catch (NoSuchEntityException $noSuchEntityException) {
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
        } catch (NoSuchEntityException $noSuchEntityException) {
            throw new HttpBadRequestException(
                $request,
                'Failed to find location by id "' . $id . '".',
                $noSuchEntityException
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