@extends('admin.layouts.app')

@section('title', 'Produits')

@section('content')
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold font-mono text-brutal-dark">📦 PRODUITS</h1>
        <a href="{{ route('admin.products.create') }}"
            class="bg-brutal-orange text-white brutal-border brutal-shadow px-6 py-3 font-mono font-bold hover:translate-x-[-2px] hover:translate-y-[-2px] transition-all">
            + NOUVEAU PRODUIT
        </a>
    </div>

    <div class="bg-white brutal-border brutal-shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-brutal-dark text-white">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-mono font-bold uppercase">Image</th>
                    <th class="px-6 py-4 text-left text-xs font-mono font-bold uppercase">Nom</th>
                    <th class="px-6 py-4 text-left text-xs font-mono font-bold uppercase">Catégorie</th>
                    <th class="px-6 py-4 text-left text-xs font-mono font-bold uppercase">Prix</th>
                    <th class="px-6 py-4 text-left text-xs font-mono font-bold uppercase">Statut</th>
                    <th class="px-6 py-4 text-right text-xs font-mono font-bold uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y-2 divide-brutal-dark">
                @forelse($products as $product)
                    <tr class="hover:bg-brutal-yellow/20 transition-colors">
                        <td class="px-6 py-4">
                            @if($product->images->first())
                                <img src="{{ asset('storage/' . $product->images->first()->path) }}" alt="{{ $product->name }}"
                                    class="w-16 h-16 object-cover brutal-border">
                            @else
                                <div class="w-16 h-16 bg-gray-200 brutal-border flex items-center justify-center text-2xl">
                                    📷
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-brutal-dark">{{ $product->name }}</div>
                            <div class="text-sm text-gray-500">{{ Str::limit($product->description, 50) }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-mono font-bold bg-gray-100 brutal-border">
                                {{ $product->category?->name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-mono font-bold text-brutal-orange text-lg">
                            {{ number_format($product->price, 0) }} DA
                        </td>
                        <td class="px-6 py-4">
                            @if($product->is_active)
                                <span class="px-3 py-1 text-xs font-mono font-bold bg-green-300 brutal-border">✓ ACTIF</span>
                            @else
                                <span class="px-3 py-1 text-xs font-mono font-bold bg-red-300 brutal-border">✗ INACTIF</span>
                            @endif
                            @if($product->is_featured)
                                <span class="px-3 py-1 text-xs font-mono font-bold bg-brutal-yellow brutal-border ml-2">⭐ FEATURED</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('admin.products.edit', $product) }}"
                                class="inline-block px-4 py-2 bg-blue-100 brutal-border text-brutal-dark font-mono font-bold text-sm hover:bg-blue-200">
                                ✏️ Modifier
                            </a>
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Supprimer ce produit?')"
                                    class="px-4 py-2 bg-red-100 brutal-border text-brutal-dark font-mono font-bold text-sm hover:bg-red-200">
                                    🗑️
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="text-4xl mb-4">📦</div>
                            <p class="text-gray-500 mb-4">Aucun produit</p>
                            <a href="{{ route('admin.products.create') }}" class="text-brutal-orange font-mono font-bold underline">
                                Créer un produit
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $products->links() }}
    </div>
@endsection