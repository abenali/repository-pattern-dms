<?php

declare(strict_types=1);

namespace App\Presentation\Controller\Api;

use App\Application\UseCase\SearchDocuments\SearchDocumentsCommand;
use App\Application\UseCase\SearchDocuments\SearchDocumentsHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/documents', name: 'api_documents_')]
final class DocumentController extends AbstractController
{
    public function __construct(
        private SearchDocumentsHandler $searchHandler,
    ) {
    }

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        try {
            // 1. Extract filters from query params
            $filters = [];

            if ($request->query->has('authorId')) {
                $filters['authorId'] = $request->query->get('authorId');
            }

            if ($request->query->has('status')) {
                $filters['status'] = $request->query->get('status');
            }

            if ($request->query->has('tags')) {
                $filters['tags'] = $request->query->all('tags');
            }

            if ($request->query->has('createdAfter')) {
                $filters['createdAfter'] = $request->query->get('createdAfter');
            }

            if ($request->query->has('createdBefore')) {
                $filters['createdBefore'] = $request->query->get('createdBefore');
            }

            if ($request->query->has('fileType')) {
                $filters['fileType'] = $request->query->get('fileType');
            }

            // 2. Extract pagination and sorting
            $orderBy = $request->query->get('orderBy');
            $orderDirection = strtoupper($request->query->get('orderDirection', 'ASC'));
            $page = max(1, (int) $request->query->get('page', 1));
            $limit = min(100, max(1, (int) $request->query->get('limit', 20)));

            // 3. Create command
            $command = new SearchDocumentsCommand(
                filters: $filters,
                orderBy: $orderBy,
                orderDirection: $orderDirection,
                page: $page,
                limit: $limit
            );

            // 4. Execute use case
            $response = $this->searchHandler->execute($command);

            // 5. Return JSON response
            return $this->json($response->toArray(), Response::HTTP_OK);
        } catch (\InvalidArgumentException $e) {
            return $this->json(
                ['error' => 'Invalid parameters: '.$e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\RuntimeException $e) {
            // User not found, etc.
            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        } catch (\Throwable $e) {
            return $this->json(
                ['error' => 'Internal server error: '.$e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
