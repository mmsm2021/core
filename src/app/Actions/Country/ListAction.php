<?php

namespace App\Actions\Country;

use App\Database\Repositories\CountryRepository;
use Doctrine\Common\Collections\Criteria;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class ListAction
{
    /**
     * @var JsonResponseFactory
     */
    private JsonResponseFactory $jsonResponseFactory;

    /**
     * @var CountryRepository
     */
    private CountryRepository $countryRepository;

    /**
     * ListAction constructor.
     * @param JsonResponseFactory $jsonResponseFactory
     * @param CountryRepository $countryRepository
     */
    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        CountryRepository $countryRepository
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->countryRepository = $countryRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/countries",
     *     summary="Returns array of countries",
     *     tags={"Country"},
     *     @OA\Parameter(
     *         name="iso3",
     *         in="query",
     *         description="The ISO-3 code of the country.",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             minLength=3,
     *             maxLength=3,
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="The name or part of the name of a country",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Will reply with the countries in JSON format",
     *         @OA\JsonContent(ref="#/components/schemas/CountryList")
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
        $params = $request->getQueryParams();

        $criteria = Criteria::create();
        $and = false;
        $this->search('iso3', $params, $criteria, $and);
        $this->search('name', $params, $criteria, $and);

        return $this->jsonResponseFactory->create(
            200,
            $this->countryRepository->getList($criteria, true)->toArray(),
        );
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