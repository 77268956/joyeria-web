<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return view('admin.customers.index', ['customers' => $customers]);
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
    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $customer = Customer::create([
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
        } catch (Throwable $exception) {
            return back()->withInput()->withErrors(['error' => 'No se pudo registrar el cliente: '.$exception->getMessage()]);
        }

        activity('clientes')
            ->performedOn($customer)
            ->causedBy($request->user())
            ->log("Cliente registrado: {$customer->full_name}");

        return redirect()->route('admin.customers.show', $customer)
            ->with('status', "El cliente '{$customer->full_name}' se registró correctamente.");
    }

    /**
     * Display a customer with their sales history.
     */
    public function show(Customer $customer): View
    {
        $customer->load(['sales' => fn ($query) => $query->latest('id')->limit(20)]);

        return view('admin.customers.show', ['customer' => $customer]);
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
