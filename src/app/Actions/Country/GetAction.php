<?php

namespace App\Actions\Country;

use App\Database\Repositories\CountryRepository;
use App\Exceptions\EntityNotFoundException;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;

class GetAction
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
     * GetAction constructor.
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
     *     path="/api/v1/countries/{iso3}",
     *     summary="Returns a JSON object of a country",
     *     tags={"Country"},
     *     @OA\Parameter(
     *         name="iso3",
     *         in="path",
     *         description="The iso3 of the country.",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             minLength=3,
     *             maxLength=3,
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Will reply with the location in JSON format",
     *         @OA\JsonContent(ref="#/components/schemas/Country")
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
     * @param string $iso3
     * @return Response
     * @throws HttpNotFoundException
     */
    public function __invoke(Request $request, string $iso3): Response
    {
        try {
            return $this->jsonResponseFactory->create(200,
                $this->countryRepository->getByIso3($iso3)->toArray()
            );
        } catch (EntityNotFoundException $entityNotFoundException) {
            throw new HttpNotFoundException(
                $request,
                $entityNotFoundException->getMessage(),
                $entityNotFoundException
            );
        }
    }
}