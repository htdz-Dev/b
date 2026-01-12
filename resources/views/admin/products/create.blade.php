@extends('admin.layouts.app')

@section('title', 'Nouveau Produit')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.products.index') }}" class="text-gray-500 hover:text-gray-700">← Retour aux produits</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-2">Nouveau Produit</h1>
    </div>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold mb-4">Informations</h2>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Nom du produit *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Catégorie *</label>
                        <select name="category_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                            <option value="">Sélectionner...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Description</label>
                        <textarea name="description" rows="4"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Prix ($) *</label>
                        <input type="number" name="price" value="{{ old('price') }}" step="0.01" min="0" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>

                <!-- Variants -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold mb-4">Variantes (Tailles / Couleurs)</h2>
                    <p class="text-gray-500 text-sm mb-4">Ajoutez les différentes options disponibles</p>

                    <div id="variants-container">
                        <div class="variant-row grid grid-cols-4 gap-4 mb-3 items-end">
                            <div>
                                <label class="block text-gray-600 text-xs mb-1">Taille</label>
                                <input type="text" name="variants[0][size]" placeholder="ex: L, XL"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-gray-600 text-xs mb-1">Couleur</label>
                                <input type="text" name="variants[0][color]" placeholder="ex: Black"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-gray-600 text-xs mb-1">Stock</label>
                                <input type="number" name="variants[0][stock_quantity]" value="10"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-gray-600 text-xs mb-1">Prix +/-</label>
                                <input type="number" name="variants[0][price_adjustment]" value="0" step="0.01"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                        </div>
                    </div>

                    <button type="button" onclick="addVariant()"
                        class="text-amber-600 hover:text-amber-700 text-sm font-medium mt-2">
                        + Ajouter une variante
                    </button>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold mb-4">Statut</h2>

                    <label class="flex items-center mb-3">
                        <input type="checkbox" name="is_active" value="1" checked
                            class="w-4 h-4 text-amber-500 rounded border-gray-300">
                        <span class="ml-2 text-gray-700">Produit actif</span>
                    </label>

                    <label class="flex items-center">
                        <input type="checkbox" name="is_featured" value="1"
                            class="w-4 h-4 text-amber-500 rounded border-gray-300">
                        <span class="ml-2 text-gray-700">Produit en vedette</span>
                    </label>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold mb-4">📷 Images</h2>
                    <input type="file" name="images[]" accept="image/*" multiple
                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                    <p class="text-xs text-gray-500 mt-2">Vous pouvez sélectionner plusieurs images (Ctrl+Click). La
                        première sera l'image principale.</p>
                </div>

                <button type="submit"
                    class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 rounded-lg transition">
                    Créer le produit
                </button>
            </div>
        </div>
    </form>

    <script>
        let variantIndex = 1;
        function addVariant() {
            const container = document.getElementById('variants-container');
            const html = `
                <div class="variant-row grid grid-cols-4 gap-4 mb-3 items-end">
                    <div>
                        <input type="text" name="variants[${variantIndex}][size]" placeholder="ex: L, XL"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <input type="text" name="variants[${variantIndex}][color]" placeholder="ex: Black"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <input type="number" name="variants[${variantIndex}][stock_quantity]" value="10"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <input type="number" name="variants[${variantIndex}][price_adjustment]" value="0" step="0.01"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            variantIndex++;
        }
    </script>
@endsection