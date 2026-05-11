<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Restaurant\Enums\TableStatus;
use Modules\Restaurant\Models\Table;
use Modules\Restaurant\Repositories\TableRepository;

class TableController extends Controller
{
    public function __construct(
        private readonly TableRepository $tables,
    ) {}

    public function index(): Response
    {
        $tables = $this->tables->getForTenant(auth()->user()->tenant_id);

        return Inertia::render('restaurant::Tables/Index', [
            'tables' => $tables->map(fn (Table $table) => [
                'id' => $table->id,
                'name' => $table->name,
                'capacity' => $table->capacity,
                'status' => $table->status->value,
                'position_x' => $table->position_x,
                'position_y' => $table->position_y,
                'active_order' => $table->orders->firstWhere('status', '<', 4),
            ]),
            'statuses' => TableStatus::class,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'position_x' => ['nullable', 'integer'],
            'position_y' => ['nullable', 'integer'],
        ]);

        $data['tenant_id'] = auth()->user()->tenant_id;

        Table::create($data);

        return redirect()->route('restaurant.tables.index');
    }

    public function update(Request $request, Table $table): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'status' => ['required', 'integer', 'in:1,2,3'],
            'position_x' => ['nullable', 'integer'],
            'position_y' => ['nullable', 'integer'],
        ]);

        $table->update($data);

        return redirect()->route('restaurant.tables.index');
    }

    public function destroy(Table $table): RedirectResponse
    {
        $table->delete();

        return redirect()->route('restaurant.tables.index');
    }
}
