@extends('admin.layouts.app')

@section('title', 'Commande #' . $order->id)

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.orders.index') }}" class="text-gray-500 hover:text-brutal-dark font-mono">← Retour aux
            commandes</a>
        <h1 class="text-3xl font-bold font-mono text-brutal-dark mt-2">🛒 Commande #{{ $order->id }}</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Items -->
            <div class="bg-white brutal-border brutal-shadow">
                <div class="p-6 border-b-3 border-brutal-dark bg-brutal-dark text-white">
                    <h2 class="text-lg font-bold font-mono">📦 ARTICLES</h2>
                </div>
                <div class="p-6">
                    @foreach($order->items as $item)
                        <div
                            class="flex items-center justify-between py-4 {{ !$loop->last ? 'border-b-2 border-brutal-dark' : '' }}">
                            <div class="flex items-center">
                                <div class="w-16 h-16 brutal-border flex items-center justify-center mr-4 bg-gray-50">
                                    @if($item->product?->images->first())
                                        <img src="{{ asset('storage/' . $item->product->images->first()->path) }}"
                                            class="w-full h-full object-cover">
                                    @else
                                        <span class="text-2xl">📦</span>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-bold">{{ $item->product?->name ?? 'Produit supprimé' }}</div>
                                    @if($item->variant)
                                        <div class="text-sm text-gray-500 font-mono">
                                            {{ $item->variant->size ?? '' }} {{ $item->variant->color ?? '' }}
                                        </div>
                                    @endif
                                    <div class="text-sm text-gray-500 font-mono">Qté: {{ $item->quantity }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold font-mono text-brutal-orange text-lg">
                                    {{ number_format($item->price * $item->quantity, 0) }} DA</div>
                                <div class="text-sm text-gray-500 font-mono">{{ number_format($item->price, 0) }} DA / unité
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="p-6 bg-brutal-yellow border-t-3 border-brutal-dark">
                    <div class="flex justify-between text-xl">
                        <span class="font-bold font-mono">TOTAL</span>
                        <span class="font-bold font-mono text-brutal-dark">{{ number_format($order->total, 0) }} DA</span>
                    </div>
                </div>
            </div>

            <!-- Shipping -->
            <div class="bg-white brutal-border brutal-shadow p-6">
                <h2 class="text-lg font-bold font-mono mb-4">📍 ADRESSE DE LIVRAISON</h2>
                <div class="text-gray-700 space-y-1">
                    <p class="font-bold text-lg">{{ $order->shipping_name }}</p>
                    <p>{{ $order->shipping_address }}</p>
                    <p>{{ $order->shipping_city }}, {{ $order->shipping_wilaya }}</p>
                    @if($order->shipping_postal_code)
                        <p>{{ $order->shipping_postal_code }}</p>
                    @endif
                    <p class="mt-3 font-mono font-bold">📞 {{ $order->shipping_phone }}</p>
                </div>
                @if($order->notes)
                    <div class="mt-4 p-3 bg-gray-100 brutal-border">
                        <p class="text-sm font-mono font-bold">📝 Notes:</p>
                        <p class="text-gray-700">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-white brutal-border brutal-shadow p-6">
                <h2 class="text-lg font-bold font-mono mb-4">⚡ STATUT</h2>

                <form action="{{ route('admin.orders.status', $order) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <select name="status"
                        class="w-full px-4 py-3 brutal-border bg-white font-mono font-bold focus:ring-2 focus:ring-brutal-yellow mb-4">
                        <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>⏳ En attente</option>
                        <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>✓ Confirmée</option>
                        <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>🔧 En préparation
                        </option>
                        <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>🚚 Expédiée</option>
                        <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>✅ Livrée</option>
                        <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>❌ Annulée</option>
                    </select>

                    <button type="submit"
                        class="w-full bg-brutal-orange text-white font-mono font-bold py-3 brutal-border brutal-shadow hover:translate-x-[-2px] hover:translate-y-[-2px] transition-all">
                        METTRE À JOUR
                    </button>
                </form>
            </div>

            <div class="bg-white brutal-border brutal-shadow p-6">
                <h2 class="text-lg font-bold font-mono mb-4">📋 INFORMATIONS</h2>
                <div class="space-y-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-mono">Date</span>
                        <span class="font-mono font-bold">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-mono">N° Commande</span>
                        <span class="font-mono font-bold">{{ $order->order_number }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 font-mono">Paiement</span>
                        <span class="px-2 py-1 text-xs font-mono font-bold brutal-border
                                @if($order->payment_method === 'chargily') bg-green-200 @else bg-gray-100 @endif">
                            {{ $order->payment_method === 'chargily' ? '💳 CHARGILY' : '💵 COD' }}
                        </span>
                    </div>
                    @if($order->payment_status === 'paid')
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 font-mono">Statut paiement</span>
                            <span class="px-2 py-1 text-xs font-mono font-bold brutal-border bg-green-300">✓ PAYÉ</span>
                        </div>
                    @endif
                    @if($order->user)
                        <div class="flex justify-between">
                            <span class="text-gray-500 font-mono">Client</span>
                            <span class="font-mono">{{ $order->user->email }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection