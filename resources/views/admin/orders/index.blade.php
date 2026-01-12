@extends('admin.layouts.app')

@section('title', 'Commandes')

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl font-bold font-mono text-brutal-dark">🛒 COMMANDES</h1>
    </div>

    <div class="bg-white brutal-border brutal-shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-brutal-dark text-white">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-mono font-bold uppercase">#</th>
                    <th class="px-6 py-4 text-left text-xs font-mono font-bold uppercase">Client</th>
                    <th class="px-6 py-4 text-left text-xs font-mono font-bold uppercase">Total</th>
                    <th class="px-6 py-4 text-left text-xs font-mono font-bold uppercase">Paiement</th>
                    <th class="px-6 py-4 text-left text-xs font-mono font-bold uppercase">Statut</th>
                    <th class="px-6 py-4 text-left text-xs font-mono font-bold uppercase">Date</th>
                    <th class="px-6 py-4 text-right text-xs font-mono font-bold uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y-2 divide-brutal-dark">
                @forelse($orders as $order)
                    <tr class="hover:bg-brutal-yellow/20 transition-colors">
                        <td class="px-6 py-4 font-mono font-bold text-brutal-dark">#{{ $order->id }}</td>
                        <td class="px-6 py-4">
                            <div class="font-bold">{{ $order->shipping_name }}</div>
                            <div class="text-sm text-gray-500">{{ $order->user?->email ?? $order->shipping_phone }}</div>
                        </td>
                        <td class="px-6 py-4 font-mono font-bold text-brutal-orange text-lg">
                            {{ number_format($order->total, 0) }} DA</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-mono font-bold brutal-border
                                        @if($order->payment_method === 'chargily') bg-green-200 @else bg-gray-100 @endif">
                                {{ $order->payment_method === 'chargily' ? '💳 CHARGILY' : '💵 COD' }}
                            </span>
                            @if($order->payment_status === 'paid')
                                <span class="ml-1 text-green-600 font-bold">✓</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-mono font-bold brutal-border
                                        @if($order->status === 'delivered') bg-green-300
                                        @elseif($order->status === 'cancelled') bg-red-300
                                        @elseif($order->status === 'shipped') bg-blue-300
                                        @elseif($order->status === 'processing') bg-purple-300
                                        @elseif($order->status === 'confirmed') bg-teal-300
                                        @else bg-brutal-yellow @endif">
                                {{ strtoupper($order->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 font-mono">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}"
                                class="inline-block px-4 py-2 bg-blue-100 brutal-border text-brutal-dark font-mono font-bold text-sm hover:bg-blue-200">
                                👁️ Voir
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-4xl mb-4">🛒</div>
                            <p class="text-gray-500">Aucune commande</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $orders->links() }}
    </div>
@endsection