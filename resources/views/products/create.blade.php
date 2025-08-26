<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Add Product</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  @vite('resources/css/app.css') {{-- Tailwind build (laravel mix/vite) --}}
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
  <div class="max-w-5xl mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-4">Add Product</h1>

    @if(session('success'))
      <div class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-green-800">
        {{ session('success') }}
      </div>
    @endif

    @if ($errors->any())
      <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-red-800">
          <ul class="list-disc pl-5">
              @foreach ($errors->all() as $error)
                 <li>{{ $error }}</li>
              @endforeach
          </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('products.store') }}" class="space-y-6">
      @csrf

      {{-- Company (read-only) --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium mb-1">Company</label>
          <input type="text" value="{{ $companyName }}" class="w-full rounded-md border bg-gray-100 px-3 py-2" disabled>
          <input type="hidden" name="company_id" value="{{ $companyId }}"> {{-- server-side override anyway --}}
        </div>

        <x-search-select
          label="Category"
          name="category_id"
          :options="$categories"
          placeholder="Type category name..."
        />
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-search-select
          label="Brand"
          name="brand_id"
          :options="$brands"
          placeholder="Type brand name..."
        />

        <x-search-select
          label="Unit *"
          name="unit_id"
          :options="$units"
          placeholder="Type unit (e.g. pc, kg)..."
          required="true"
        />
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-search-select
          label="Tax rate"
          name="tax_rate_id"
          :options="$taxRates"
          placeholder="Type tax rate name..."
        />

        <div>
          <label class="block text-sm font-medium mb-1">SKU *</label>
          <input name="sku" required class="w-full rounded-md border px-3 py-2" placeholder="e.g. TV-43-GP-001" value="{{ old('sku') }}">
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium mb-1">Name *</label>
          <input name="name" required class="w-full rounded-md border px-3 py-2" placeholder="Product name" value="{{ old('name') }}">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Barcode</label>
          <input name="barcode" class="w-full rounded-md border px-3 py-2" placeholder="EAN/UPC" value="{{ old('barcode') }}">
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium mb-1">Cost price *</label>
          <input name="cost_price" type="number" step="0.01" min="0" required class="w-full rounded-md border px-3 py-2" value="{{ old('cost_price',0) }}">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Sell price *</label>
          <input name="sell_price" type="number" step="0.01" min="0" required class="w-full rounded-md border px-3 py-2" value="{{ old('sell_price',0) }}">
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium mb-1">Min stock *</label>
          <input name="min_stock" type="number" step="0.001" min="0" required class="w-full rounded-md border px-3 py-2" value="{{ old('min_stock',0) }}">
        </div>
        <div class="flex items-end">
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-gray-300" checked>
            <span>Is active</span>
          </label>
        </div>
      </div>

      <div class="flex gap-3">
        <button class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Create</button>
        <a href="/" class="rounded-md border px-4 py-2">Cancel</a>
      </div>
    </form>
  </div>
</body>
</html>
