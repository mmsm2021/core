<?php

namespace App\Actions\Location;

use App\Database\Repositories\LocationRepository;
use App\Exceptions\DeleteException;
use App\Exceptions\NoSuchEntityException;
use MMSM\Lib\Authorizer;
use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SimpleJWT\JWT;
use Slim\Exception\HttpUnauthorizedException;
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
                'message' => 'Entity is gone.',
            ]);
        } catch (DeleteException $exception) {
            return $this->jsonResponseFactory->create(500, [
                'error' => true,
                'message' => $exception->getMessage(),
            ]);
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