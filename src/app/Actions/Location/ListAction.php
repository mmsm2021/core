<?php

namespace App\Actions\Location;

use App\Database\Entities\Country;
use App\Database\Repositories\CountryRepository;
use App\Database\Repositories\LocationRepository;
use App\Exceptions\EntityNotFoundException;
use Doctrine\Common\Collections\Criteria;
use MMSM\Lib\Authorizer;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

class ListAction
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
     * @var CountryRepository
     */
    private CountryRepository $countryRepository;

    /**
     * Get constructor.
     * @param JsonResponseFactory $jsonResponseFactory
     * @param LocationRepository $locationRepository
     * @param Authorizer $authorizer
     * @param CountryRepository $countryRepository
     */
    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        LocationRepository $locationRepository,
        Authorizer $authorizer,
        CountryRepository $countryRepository
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->locationRepository = $locationRepository;
        $this->authorizer = $authorizer;
        $this->countryRepository = $countryRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/locations",
     *     summary="Returns array of locations",
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
     *         name="size",
     *         in="query",
     *         description="The amount of Locations returned",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=20
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="offset",
     *         in="query",
     *         description="The amount of Locations to skip",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=0
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Search for name.",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="street",
     *         in="query",
     *         description="Search for street.",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="number",
     *         in="query",
     *         description="Search for number.",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="zipcode",
     *         in="query",
     *         description="Search for zipcde.",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="city",
     *         in="query",
     *         description="Search for city.",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="state",
     *         in="query",
     *         description="Search for state.",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="country",
     *         in="query",
     *         description="Search for country.",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="deleted",
     *         in="query",
     *         description="Include deleted (requires Authorization header).",
     *         required=false,
     *         @OA\Schema(
     *             type="boolean"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Will reply with the locations in JSON format",
     *         @OA\JsonContent(ref="#/components/schemas/LocationList")
     *     ),
     *     @OA\Response(
     *         response=400,
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
     */
    public function __invoke(Request $request): Response
    {
        return $this->jsonResponseFactory->create(200, $this->locationRepository->getList(
            $this->getQueryCriteria($request),
            true
        )->toArray());
    }

    /**
     * @param Request $request
     * @return Criteria
     */
    protected function getQueryCriteria(Request $request): Criteria
    {
        $criteria = Criteria::create();
        $params = $request->getQueryParams();
        $size = 20;
        $offset = 0;
        if (v::arrayType()->key('size', v::oneOf(v::intType(), v::stringType()->numericVal()))->validate($params)) {
            $size = (int)$params['size'];
        }
        if ($size < 20 ) {
            $size = 20;
        }
        if ($size > 200) {
            $size = 200;
        }
        if (v::arrayType()->key('offset', v::oneOf(v::intType(), v::stringType()->numericVal()))->validate($params)) {
            $offset = (int)$params['offset'];
        }
        if ($offset < 0) {
            $offset = 0;
        }

        $criteria->setMaxResults($size);
        $criteria->setFirstResult($offset);

        $and = false;
        $this->search('name', $params, $criteria, $and);
        $this->search('street', $params, $criteria, $and);
        $this->search('number', $params, $criteria, $and);
        $this->search('zipcode', $params, $criteria, $and);
        $this->search('city', $params, $criteria, $and);
        $this->search('state', $params, $criteria, $and);

        if (v::arrayType()->key('country', v::stringType()->notEmpty())->validate($params)) {
            try {
                $country = $this->countryRepository->getByIso3($params['country']);
            } catch (EntityNotFoundException $noSuchEntityException) {
                try {
                    $country = $this->countryRepository->getByName($params['country']);
                } catch (EntityNotFoundException $noSuchEntityException) {
                    $country = false;
                }
            }
            if ($country instanceof Country) {
                if ($and) {
                    $criteria->andWhere(Criteria::expr()->eq('country', $country));
                } else {
                    $criteria->where(Criteria::expr()->eq('country', $country));
                }
            }
        }

        $includeDeleted = v::arrayType()
                ->key('deleted', v::stringType()->equals('true'))->validate($params) &&
            $this->authorizer->hasRole($request, 'user.roles.super', false);

        if (!$includeDeleted) {
            if ($and) {
                $criteria->andWhere(Criteria::expr()->isNull('deletedAt'));
            } else {
                $criteria->where(Criteria::expr()->isNull('deletedAt'));
                $and = true;
            }
        }

        return $criteria;
    }

    /**
     * @param string $name
     * @param array $params
     * @param Criteria $criteria
     * @param bool $and
     */
    public function search(
        string $name,
        array $params,
        Criteria $criteria,
        bool &$and
    ): void {
        if (v::arrayType()->key($name, v::stringType()->notEmpty())->validate($params)) {
            if ($and) {
                $criteria->andWhere(Criteria::expr()->contains($name, $params[$name]));
            } else {
                $criteria->where(Criteria::expr()->contains($name, $params[$name]));
                $and = true;
            }
        }
    }
}