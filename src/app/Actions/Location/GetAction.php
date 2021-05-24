<?php

namespace App\Actions\Location;

use MMSM\Lib\Factories\JsonResponseFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class GetAction
{
    /**
     * @var JsonResponseFactory
     */
    private JsonResponseFactory $jsonResponseFactory;

    /**
     * Get constructor.
     * @param JsonResponseFactory $jsonResponseFactory
     */
    public function __construct(JsonResponseFactory $jsonResponseFactory)
    {
        $this->jsonResponseFactory = $jsonResponseFactory;
    }

    /**
     * @param Request $request
     * @param string $id
     * @return Response
     */
    public function __invoke(Request $request, string $id): Response
    {
        return $this->jsonResponseFactory->create(200, [
            'test' => true,
            'something' => 'nothing',
        ]);
    }
}