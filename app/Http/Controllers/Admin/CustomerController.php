<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LayawayStatus;
use App\Enums\SaleStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CustomerController extends Controller
{
    /**
     * List the customers of the store with search.
     */
    public function index(Request $request): View
    {
        $customers = Customer::query()
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $term = '%'.$request->input('search').'%';
                $query->where(function (Builder $query) use ($term): void {
                    $query->where('full_name', 'like', $term)
                        ->orWhere('document_number', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->withCount('sales')
            ->orderBy('full_name')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => Customer::query()->count(),
            'with_sales' => Customer::query()->has('sales')->count(),
            'new_this_month' => Customer::query()->where('created_at', '>=', now()->startOfMonth())->count(),
            'with_contact' => Customer::query()->where(function (Builder $query): void {
                $query->where(function (Builder $sub): void {
                    $sub->whereNotNull('phone')->where('phone', '!=', '');
                })->orWhere(function (Builder $sub): void {
                    $sub->whereNotNull('email')->where('email', '!=', '');
                });
            })->count(),
        ];

        return view('admin.customers.index', [
            'customers' => $customers,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form to register a new customer.
     */
    public function create(): View
    {
        return view('admin.customers.create');
    }

    /**
     * Store a new customer, keeping the ID photo on the private disk.
     */
    public function store(StoreCustomerRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $storeId = $user?->store_id;

        try {
            $customer = DB::transaction(function () use ($validated, $request, $storeId) {
                $existing = Customer::query()
                    ->where('store_id', $storeId)
                    ->where('document_type', $validated['document_type'])
                    ->where('document_number', $validated['document_number'])
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    return $existing;
                }

                return Customer::create([
                    'full_name' => $validated['full_name'],
                    'document_type' => $validated['document_type'],
                    'document_number' => $validated['document_number'],
                    'phone' => $validated['phone'] ?? null,
                    'email' => $validated['email'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'birth_date' => $validated['birth_date'] ?? null,
                    'id_photo_path' => $request->hasFile('id_photo')
                        ? $request->file('id_photo')->store('customer-ids', 'local')
                        : null,
                    'notes' => $validated['notes'] ?? null,
                ]);
            });
        } catch (Throwable $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No se pudo registrar el cliente: '.$exception->getMessage()], 422);
            }

            return back()->withInput()->withErrors(['error' => 'No se pudo registrar el cliente: '.$exception->getMessage()]);
        }

        $wasRecentlyCreated = $customer->wasRecentlyCreated;

        if ($wasRecentlyCreated) {
            activity('clientes')
                ->performedOn($customer)
                ->causedBy($user)
                ->log("Cliente registrado: {$customer->full_name}");
        }

        if ($request->expectsJson()) {
            return response()->json([
                'customer' => [
                    'id' => $customer->id,
                    'full_name' => $customer->full_name,
                    'document' => trim($customer->document_type.' '.$customer->document_number),
                    'phone' => $customer->phone,
                ],
                'created' => $wasRecentlyCreated,
                'message' => $wasRecentlyCreated
                    ? "El cliente '{$customer->full_name}' se registró correctamente."
                    : "El cliente '{$customer->full_name}' ya existe en esta sucursal.",
            ], $wasRecentlyCreated ? 201 : 200);
        }

        if ($wasRecentlyCreated) {
            return redirect()->route('admin.customers.show', $customer)
                ->with('status', "El cliente '{$customer->full_name}' se registró correctamente.");
        }

        return redirect()->route('admin.customers.show', $customer)
            ->with('status', "El cliente '{$customer->full_name}' ya existe en esta sucursal.");
    }

    /**
     * Display a customer with their sales history.
     */
    public function show(Customer $customer): View
    {
        $customer->load([
            'sales' => fn ($query) => $query->latest('id')->limit(30),
            'layaways' => fn ($query) => $query->latest('id')->limit(10),
        ]);

        $completedSales = $customer->sales->where('status', SaleStatus::Completed);
        $completedCount = $completedSales->count();
        $totalSpent = $completedSales->reduce(
            fn (string $carry, $sale): string => bcadd($carry, (string) $sale->total, 2),
            '0.00'
        );
        $averageTicket = $completedCount > 0
            ? bcdiv($totalSpent, (string) $completedCount, 2)
            : '0.00';

        return view('admin.customers.show', [
            'customer' => $customer,
            'stats' => [
                'total_sales' => $completedCount,
                'total_spent' => $totalSpent,
                'average_ticket' => $averageTicket,
                'active_layaways' => $customer->layaways->where('status', LayawayStatus::Active)->count(),
            ],
        ]);
    }

    /**
     * Show the form to edit a customer.
     */
    public function edit(Customer $customer): View
    {
        return view('admin.customers.edit', ['customer' => $customer]);
    }

    /**
     * Update a customer's data.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validated();

        $data = [
            'full_name' => $validated['full_name'],
            'document_type' => $validated['document_type'],
            'document_number' => $validated['document_number'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];

        if ($request->hasFile('id_photo')) {
            if ($customer->id_photo_path) {
                Storage::disk('local')->delete($customer->id_photo_path);
            }

            $data['id_photo_path'] = $request->file('id_photo')->store('customer-ids', 'local');
        }

        $customer->update($data);

        activity('clientes')
            ->performedOn($customer)
            ->causedBy($request->user())
            ->log("Cliente actualizado: {$customer->full_name}");

        return redirect()->route('admin.customers.show', $customer)
            ->with('status', 'La ficha del cliente se actualizó correctamente.');
    }

    /**
     * Soft delete a customer.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        $name = $customer->full_name;
        $customer->delete();

        activity('clientes')
            ->performedOn($customer)
            ->causedBy(request()->user())
            ->log("Cliente eliminado: {$name}");

        return redirect()->route('admin.customers.index')
            ->with('status', "El cliente '{$name}' se eliminó.");
    }

    /**
     * Search customers by name, document or phone (used by the POS).
     */
    public function search(Request $request): JsonResponse
    {
        $term = '%'.$request->string('q')->trim()->value().'%';

        $customers = Customer::query()
            ->where(function (Builder $query) use ($term): void {
                $query->where('full_name', 'like', $term)
                    ->orWhere('document_number', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            })
            ->orderBy('full_name')
            ->limit(15)
            ->get(['id', 'full_name', 'document_type', 'document_number', 'phone'])
            ->map(fn (Customer $customer): array => [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
                'document' => $customer->document_type.' '.$customer->document_number,
                'phone' => $customer->phone,
            ]);

        return response()->json(['customers' => $customers]);
    }

    /**
     * Stream a customer's ID photo from the private disk (authorized only).
     */
    public function photo(Customer $customer): StreamedResponse
    {
        abort_if(blank($customer->id_photo_path) || ! Storage::disk('local')->exists($customer->id_photo_path), 404);

        return Storage::disk('local')->response($customer->id_photo_path);
    }
}
