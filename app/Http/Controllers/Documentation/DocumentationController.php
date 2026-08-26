<?php

namespace App\Http\Controllers\Documentation;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentationResource;
use App\Models\Documentation;
use App\Models\FarmDocument;
use App\Services\DocumentationService;
use App\Services\FarmDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DocumentationController extends Controller
{
    public function __construct(
        private readonly DocumentationService $documentation,
        private readonly FarmDocumentService $farmDocuments,
    ) {
    }

    /**
     * Display the documentation page — every shared knowledge-base document
     * plus every document uploaded against a farm, in one combined table.
     */
    public function index(Request $request): Response
    {
        $authUserId = $request->user()->id;
        $isAdmin = $request->user()->isAdmin();

        $documents = collect(DocumentationResource::collection($this->documentation->all())->resolve())
            ->map(fn (array $doc): array => [
                ...$doc,
                'source' => 'documentation',
                'farm_id' => null,
                'farm_name' => null,
                'can_delete' => $doc['user_id'] === $authUserId,
            ])
            ->all();

        $farmDocuments = $this->farmDocuments->all()
            ->map(fn (FarmDocument $document): array => [
                'id' => $document->id,
                'user_id' => $document->user_id,
                'uploader_name' => $document->uploader?->name,
                'title' => $document->title,
                'description' => null,
                'category' => $document->document_type ?: 'Farm Document',
                'original_name' => $document->original_name,
                'file_url' => Storage::disk('public')->url($document->file_path),
                'mime_type' => $document->mime_type,
                'file_size' => $document->file_size,
                'created_at' => optional($document->created_at)?->toDateTimeString(),
                'updated_at' => optional($document->updated_at)?->toDateTimeString(),
                'source' => 'farm',
                'farm_id' => $document->farm_id,
                'farm_name' => $document->farm?->name,
                'can_delete' => $isAdmin || (int) $document->farm?->user_id === $authUserId,
            ])
            ->all();

        $combined = collect($documents)
            ->concat($farmDocuments)
            ->sortByDesc('created_at')
            ->values()
            ->all();

        return Inertia::render('Documentation/DocumentationPage', [
            'documents' => $combined,
            'categories' => $this->documentation->categories()->pluck('name')->values()->all(),
            'authUserId' => $authUserId,
        ]);
    }

    /**
     * Upload a new document.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', 'string', Rule::in($this->documentation->categories()->pluck('name')->all())],
            'attachment' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:10240'],
        ]);

        $file = $request->file('attachment');
        $path = $file->store('documentation-files', 'public');

        $this->documentation->create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    /**
     * Update a document's title, description, or category. Only the
     * uploader may edit it.
     */
    public function update(Request $request, Documentation $documentation): RedirectResponse
    {
        $this->authorizeOwner($request, $documentation);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', 'string', Rule::in($this->documentation->categories()->pluck('name')->all())],
        ]);

        $this->documentation->update($documentation, $validated);

        return back()->with('success', 'Document updated successfully.');
    }

    /**
     * Delete a document. Only the uploader may delete it.
     */
    public function destroy(Request $request, Documentation $documentation): RedirectResponse
    {
        $this->authorizeOwner($request, $documentation);

        $this->documentation->destroy($documentation);

        return back()->with('success', 'Document deleted successfully.');
    }

    /**
     * Ensure the authenticated user uploaded the given document.
     */
    private function authorizeOwner(Request $request, Documentation $documentation): void
    {
        if ($documentation->user_id !== $request->user()->id) {
            throw new AccessDeniedHttpException('You do not own this document.');
        }
    }
}
