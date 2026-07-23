<?php

namespace App\Http\Controllers\Documentation;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentationResource;
use App\Models\Documentation;
use App\Services\DocumentationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DocumentationController extends Controller
{
    public function __construct(private readonly DocumentationService $documentation)
    {
    }

    /**
     * Display the documentation page — every document in the shared
     * knowledge base, visible to every logged-in user.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Documentation/DocumentationPage', [
            'documents' => DocumentationResource::collection($this->documentation->all())->resolve(),
            'categories' => $this->documentation->categories()->pluck('name')->values()->all(),
            'authUserId' => $request->user()->id,
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
