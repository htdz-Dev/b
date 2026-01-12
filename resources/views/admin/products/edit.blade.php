@extends('admin.layouts.app')

@section('title', 'Modifier Produit')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.products.index') }}" class="text-gray-500 hover:text-gray-700">← Retour aux produits</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-2">Modifier: {{ $product->name }}</h1>
    </div>

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold mb-4">Informations</h2>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Nom du produit *</label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Catégorie *</label>
                        <select name="category_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Description</label>
                        <textarea name="description" rows="4"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">{{ old('description', $product->description) }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Prix ($) *</label>
                        <input type="number" name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>

                <!-- Current Variants -->
                @if($product->variants->count() > 0)
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold mb-4">Variantes Existantes</h2>
                        <div class="space-y-2">
                            @foreach($product->variants as $variant)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <span>
                                        {{ $variant->size ?? '-' }} / {{ $variant->color ?? '-' }}
                                        <span class="text-gray-500">(Stock: {{ $variant->stock_quantity }})</span>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Current Images -->
                @if($product->images->count() > 0)
                    @php
                        $availableColors = $product->variants->pluck('color')->filter()->unique()->values();
                    @endphp
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold mb-2">📷 Images Actuelles ({{ $product->images->count() }})</h2>
                        <p class="text-sm text-gray-500 mb-4">Assignez une couleur à chaque image pour l'afficher quand le client sélectionne cette couleur.</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($product->images as $image)
                                <div class="relative group">
                                    <img src="{{ asset('storage/' . $image->path) }}" class="w-full h-24 object-cover rounded-lg" id="image-{{ $image->id }}">
                                    
                                    <!-- Primary Badge Container -->
                                    <div id="badge-container-{{ $image->id }}">
                                        @if($image->is_primary)
                                            <span class="absolute top-1 left-1 px-2 py-0.5 bg-amber-500 text-white text-xs rounded">Principal</span>
                                        @endif
                                    </div>
                                    
                                    <!-- Delete Button -->
                                    <button type="button" onclick="deleteImage({{ $product->id }}, {{ $image->id }})"
                                            class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600"
                                            title="Supprimer">
                                        ✕
                                    </button>

                                    <!-- Set as Primary Button -->
                                    <button type="button" onclick="setPrimaryImage({{ $product->id }}, {{ $image->id }})"
                                            class="absolute top-1 right-8 opacity-0 group-hover:opacity-100 transition-opacity w-6 h-6 bg-yellow-400 text-white rounded-full flex items-center justify-center text-xs hover:bg-yellow-500"
                                            title="Définir comme principal">
                                        ⭐
                                    </button>

                                    <!-- Color assignment -->
                                    @if($availableColors->count() > 0)
                                        <select name="image_colors[{{ $image->id }}]" 
                                            class="w-full mt-2 text-xs px-2 py-1 border border-gray-300 rounded"
                                            onchange="updateImageColor({{ $image->id }}, this.value)">
                                            <option value="">-- Toutes couleurs --</option>
                                            @foreach($availableColors as $color)
                                                <option value="{{ $color }}" {{ $image->color === $color ? 'selected' : '' }}>
                                                    {{ $color }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold mb-4">Statut</h2>

                    <label class="flex items-center mb-3">
                        <input type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }}
                            class="w-4 h-4 text-amber-500 rounded border-gray-300">
                        <span class="ml-2 text-gray-700">Produit actif</span>
                    </label>

                    <label class="flex items-center">
                        <input type="checkbox" name="is_featured" value="1" {{ $product->is_featured ? 'checked' : '' }}
                            class="w-4 h-4 text-amber-500 rounded border-gray-300">
                        <span class="ml-2 text-gray-700">Produit en vedette</span>
                    </label>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold mb-4">📷 Ajouter des images</h2>
                    <input type="file" name="images[]" accept="image/*" multiple
                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                    <p class="text-xs text-gray-500 mt-2">Vous pouvez sélectionner plusieurs images (Ctrl+Click)</p>
                </div>

                <button type="submit"
                    class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 rounded-lg transition">
                    Sauvegarder
                </button>
            </div>
        </div>
    </form>

    <script>
        function updateImageColor(imageId, color) {
            fetch(`/admin/products/images/${imageId}/color`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ color: color })
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                // Optional: Show success feedback
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur lors de la mise à jour de la couleur');
            });
        }

        function deleteImage(productId, imageId) {
            if (!confirm('Voulez-vous vraiment supprimer cette image ?')) return;

            fetch(`/admin/products/${productId}/images/${imageId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                }
            })
            .then(response => {
                if (response.ok) {
                    // Remove the image element from the DOM
                    const imgElement = document.getElementById(`image-${imageId}`);
                    if (imgElement) {
                        imgElement.closest('.group').remove();
                    }
                } else {
                    console.error('Delete failed:', response.status, response.statusText);
                    response.text().then(text => {
                        console.error('Response body:', text);
                        alert(`Erreur lors de la suppression (${response.status}): ${text.substring(0, 100)}...`);
                    }).catch(() => {
                        alert(`Erreur lors de la suppression (${response.status})`);
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur réseau');
            });
        }

        function setPrimaryImage(productId, imageId) {
            fetch(`/admin/products/${productId}/images/${imageId}/primary`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw new Error(err.message || 'Failed to set primary image'); });
                }
                return response.json();
            })
            .then(data => {
                // Remove 'Principal' badge from all images
                document.querySelectorAll('[id^="badge-container-"]').forEach(container => {
                    container.innerHTML = '';
                });

                // Add 'Principal' badge to the newly primary image
                const primaryBadgeContainer = document.getElementById(`badge-container-${imageId}`);
                if (primaryBadgeContainer) {
                    primaryBadgeContainer.innerHTML = '<span class="absolute top-1 left-1 px-2 py-0.5 bg-amber-500 text-white text-xs rounded">Principal</span>';
                }
                alert('Image principale définie avec succès!');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur lors de la définition de l\'image principale: ' + error.message);
            });
        }
    </script>
@endsection