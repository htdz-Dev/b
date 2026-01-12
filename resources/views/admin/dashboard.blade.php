@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl font-bold font-mono text-brutal-dark">📊 DASHBOARD</h1>
        <p class="text-gray-600 mt-2">Bienvenue dans votre panneau d'administration</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white brutal-border brutal-shadow p-6">
            <div class="flex items-center">
                <div class="text-4xl mr-4">📦</div>
                <div>
                    <p class="text-sm text-gray-500 font-mono uppercase">Produits</p>
                    <p class="text-3xl font-bold font-mono text-brutal-dark">{{ $stats['products'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-brutal-yellow brutal-border brutal-shadow p-6">
            <div class="flex items-center">
                <div class="text-4xl mr-4">🛒</div>
                <div>
                    <p class="text-sm text-brutal-dark font-mono uppercase font-bold">Commandes</p>
                    <p class="text-3xl font-bold font-mono text-brutal-dark">{{ $stats['orders'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white brutal-border brutal-shadow p-6">
            <div class="flex items-center">
                <div class="text-4xl mr-4">👥</div>
                <div>
                    <p class="text-sm text-gray-500 font-mono uppercase">Utilisateurs</p>
                    <p class="text-3xl font-bold font-mono text-brutal-dark">{{ $stats['users'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-green-100 brutal-border brutal-shadow p-6">
            <div class="flex items-center">
                <div class="text-4xl mr-4">💰</div>
                <div>
                    <p class="text-sm text-green-800 font-mono uppercase font-bold">Revenus</p>
                    <p class="text-3xl font-bold font-mono text-green-800">{{ number_format($stats['revenue'], 0) }} DA</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white brutal-border brutal-shadow">
        <div class="p-6 border-b-3 border-brutal-dark bg-brutal-dark text-white">
            <h2 class="text-xl font-bold font-mono">🕒 COMMANDES RÉCENTES</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 border-b-3 border-brutal-dark">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-mono font-bold text-brutal-dark uppercase">#</th>
                        <th class="px-6 py-4 text-left text-xs font-mono font-bold text-brutal-dark uppercase">Client</th>
                        <th class="px-6 py-4 text-left text-xs font-mono font-bold text-brutal-dark uppercase">Total</th>
                        <th class="px-6 py-4 text-left text-xs font-mono font-bold text-brutal-dark uppercase">Paiement</th>
                        <th class="px-6 py-4 text-left text-xs font-mono font-bold text-brutal-dark uppercase">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-mono font-bold text-brutal-dark uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-brutal-dark">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-brutal-yellow/20 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold">#{{ $order->id }}</td>
                            <td class="px-6 py-4">
                                <div class="font-bold">{{ $order->user?->name ?? $order->shipping_name }}</div>
                                <div class="text-sm text-gray-500">{{ $order->shipping_phone }}</div>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-brutal-orange">{{ number_format($order->total, 0) }} DA</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-mono font-bold brutal-border
                                    @if($order->payment_method === 'chargily') bg-green-100 @else bg-gray-100 @endif">
                                    {{ $order->payment_method === 'chargily' ? '💳' : '💵' }}
                                </span>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">Aucune commande</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection