<?php

namespace App\Actions\Location;

use App\Database\Repositories\LocationRepository;
use App\Exceptions\DeleteException;
use App\Exceptions\NoSuchEntityException;
use MMSM\Lib\Authorizer;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;
use Throwable;

class DeleteAction
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
     * @OA\Delete(
     *     path="/api/v1/locations/{id}",
     *     summary="Delete af given location by id.",
     *     tags={"Location"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Bearer {id-token}",
     *         required=true,
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
     *     @OA\Parameter(
     *         name="hard",
     *         in="query",
     *         description="Determines if it is a hard or soft delete.",
     *         required=false,
     *         @OA\Schema(
     *             type="boolean",
     *             default=false
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Will reply with an empty body if successful.",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="will contain a JSON object with a message.",
     *         @OA\JsonContent(ref="#/components/schemas/error")
     *     ),
     *     @OA\Response(
     *         response=410,
     *         description="will contain a JSON object with a message happends if the entity could not be found by the given id.",
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
            $this->authorizer->authorizeToRole($request, 'user.roles.super');
            $query = $request->getQueryParams();
            if (!empty($query) && isset($query['hard']) && $query['hard'] == 'true') {
                $this->deleteHard($id);
                return $this->jsonResponseFactory->create(204);
            }
            $this->deleteSoft($id);
            return $this->jsonResponseFactory->create(204);
        } catch (NoSuchEntityException $exception) {
            return $this->jsonResponseFactory->create(410, [
                'error' => false,
                'message' => 'Entity is gone.',
            ]);
        } catch (DeleteException $exception) {
            throw new HttpInternalServerErrorException(
                $request,
                $exception->getMessage(),
                $exception
            );
        }
    }

    /**
     * @param string $id
     * @throws DeleteException
     * @throws NoSuchEntityException
     */
    protected function deleteHard(string $id): void
    {
        $this->locationRepository->delete(
            $this->locationRepository->getById($id, true),
            true
        );
    }

    /**
     * @param string $id
     * @throws DeleteException
     * @throws NoSuchEntityException
     */
    protected function deleteSoft(string $id): void
    {
        $this->locationRepository->delete(
            $this->locationRepository->getById($id, false),
            false
        );
    }
}