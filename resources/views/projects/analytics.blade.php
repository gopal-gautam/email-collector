<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Analytics') }} - {{ $project->name }}
        </h2>
    </x-slot>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-blue-100 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-blue-800">Total Subscriptions</h3>
                        <p class="text-2xl font-bold text-blue-600">{{ $analytics['total_subscriptions'] ?? 0 }}</p>
                    </div>
                    <div class="bg-green-100 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-green-800">Active Subscribers</h3>
                        <p class="text-2xl font-bold text-green-600">{{ $analytics['subscribed_count'] ?? 0 }}</p>
                    </div>
                    <div class="bg-yellow-100 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-yellow-800">Pending</h3>
                        <p class="text-2xl font-bold text-yellow-600">{{ $analytics['pending_count'] ?? 0 }}</p>
                    </div>
                    <div class="bg-red-100 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-red-800">Conversion Rate</h3>
                        <p class="text-2xl font-bold text-red-600">{{ $analytics['conversion_rate'] ?? 0 }}%</p>
                    </div>
                </div>

                <div class="mb-6">
                    <a href="{{ route('projects.show', $project) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Project
                    </a>
                </div>

                <div class="text-center text-gray-500">
                    <p>Detailed analytics charts and reports coming soon...</p>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>