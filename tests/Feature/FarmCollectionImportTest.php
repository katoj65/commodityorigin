<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\FarmCollection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class FarmCollectionImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_creator_can_import_collections_from_a_spreadsheet(): void
    {
        $creator = User::factory()->create();
        $farm = $this->makeFarm($creator);

        $file = $this->makeSpreadsheet([
            ['Collection Date', 'Coffee Type', 'Variety', 'Quantity', 'Unit'],
            ['2026-06-02', 'Arabica', 'SL28', 120, 'kg'],
            ['2026-06-15', 'Robusta', 'KP423', 95.5, 'bags'],
        ]);

        $response = $this->actingAs($creator)->post(route('farm.collections.import', $farm), ['file' => $file]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertSame(2, FarmCollection::query()->where('farm_id', $farm->id)->count());
        $this->assertDatabaseHas('farm_collections', [
            'farm_id' => $farm->id,
            'user_id' => $creator->id,
            'variety' => 'SL28',
            'quantity' => 120,
            'unit' => 'kg',
        ]);
    }

    public function test_a_row_missing_a_required_field_is_skipped_and_reported(): void
    {
        $creator = User::factory()->create();
        $farm = $this->makeFarm($creator);

        $file = $this->makeSpreadsheet([
            ['Collection Date', 'Coffee Type', 'Quantity', 'Unit'],
            ['2026-06-02', 'Arabica', 120, 'kg'],
            ['2026-06-15', 'Robusta', '', 'kg'],
        ]);

        $this->actingAs($creator)->post(route('farm.collections.import', $farm), ['file' => $file])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, FarmCollection::query()->where('farm_id', $farm->id)->count());
        $this->assertSame(
            ['imported' => 1, 'errors' => [['row' => 3, 'errors' => ['The quantity field is required.']]]],
            session('collection_import_result')
        );
    }

    public function test_a_row_without_coffee_type_falls_back_to_the_farms_own_coffee_type(): void
    {
        $creator = User::factory()->create();
        $farm = $this->makeFarm($creator, ['coffee_type' => 'Arabica']);

        $file = $this->makeSpreadsheet([
            ['Collection Date', 'Quantity', 'Unit'],
            ['2026-06-02', 120, 'kg'],
        ]);

        $this->actingAs($creator)->post(route('farm.collections.import', $farm), ['file' => $file])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('farm_collections', ['farm_id' => $farm->id, 'coffee_type' => 'Arabica']);
    }

    public function test_a_non_creator_cannot_import_collections(): void
    {
        $creator = User::factory()->create();
        $stranger = User::factory()->create();
        $farm = $this->makeFarm($creator);

        $file = $this->makeSpreadsheet([
            ['Collection Date', 'Quantity'],
            ['2026-06-02', 120],
        ]);

        $response = $this->actingAs($stranger)->post(route('farm.collections.import', $farm), ['file' => $file]);

        $response->assertForbidden();
        $this->assertDatabaseCount('farm_collections', 0);
    }

    public function test_the_uploaded_file_must_be_a_spreadsheet(): void
    {
        $creator = User::factory()->create();
        $farm = $this->makeFarm($creator);

        $response = $this->actingAs($creator)->post(route('farm.collections.import', $farm), [
            'file' => UploadedFile::fake()->create('collections.pdf', 10, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('farm_collections', 0);
    }

    /**
     * Build a real .xlsx UploadedFile from a grid of cell values — the
     * import goes through PhpSpreadsheet's IOFactory, which needs an
     * actual spreadsheet file on disk, not a fake blank upload.
     *
     * @param  array<int, array<int, mixed>>  $grid
     */
    private function makeSpreadsheet(array $grid): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($grid, null, 'A1');

        $path = tempnam(sys_get_temp_dir(), 'xlsx').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'collections.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    private function makeFarm(User $user, array $overrides = []): Farm
    {
        return Farm::query()->create([
            'user_id' => $user->id,
            'name' => 'Test Farm',
            ...$overrides,
        ]);
    }
}
