<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentQueryRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Services\DocumentQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DocumentController extends Controller
{
    public function index(DocumentQueryRequest $request, DocumentQuery $documents): JsonResponse
    {
        return $this->paginated($documents->build($request->user(), $request->validated(), $request->is('api/admin/*'))->paginate($request->integer('per_page', 20)), DocumentResource::class);
    }

    public function show(Request $request, Document $document): DocumentResource
    {
        $this->authorizeDocument($request, $document, 'view');
        $document->load(['category', 'product']);
        if ($request->is('api/admin/*')) {
            $document->load('owner');
        }

        return new DocumentResource($document);
    }

    private function authorizeDocument(Request $request, Document $document, string $ability): void
    {
        if (! $request->is('api/admin/*')) {
            abort_unless($document->user_id === $request->user()->id, 404);
        }
        Gate::authorize($request->is('api/admin/*') ? 'moderate' : $ability, $document);
    }
}
