<?php

namespace App\Actions\Location;

use App\Database\Repositories\LocationRepository;
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
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        return $this->jsonResponseFactory->create(200, $this->locationRepository->getList(
            $this->getQueryCriteria($request)
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
        if (v::arrayType()->key('name', v::stringType()->notEmpty())->validate($params)) {
            $criteria->where(Criteria::expr()->contains('name', $params['name']));
            $and = true;
        }

        $includeDeleted = v::arrayType()
                ->key('deleted', v::stringType()->equals('true'))->validate($params) &&
            $this->authorizer->hasRole($request, 'user.roles.super', false);

        if (!$includeDeleted) {
            if ($and) {
                $criteria->andWhere(Criteria::expr()->isNull('deletedAt'));
            } else {
                $criteria->where(Criteria::expr()->isNull('deletedAt'));
            }
        }

        return $criteria;
    }
}