<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Newsletter Projects') }}
            </h2>
            <a href="{{ route('projects.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Create New Project') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($projects->isEmpty())
                <!-- Empty State -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No newsletter projects</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating your first newsletter project.</p>
                        <div class="mt-6">
                            <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Create New Project
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <!-- Projects Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($projects as $project)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow duration-200">
                            <div class="p-6">
                                <!-- Project Header -->
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 truncate">
                                        {{ $project->name }}
                                    </h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $project->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($project->status) }}
                                    </span>
                                </div>

                                <!-- Project Description -->
                                @if($project->description)
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                        {{ $project->description }}
                                    </p>
                                @endif

                                <!-- Project Stats -->
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div class="text-center p-3 bg-blue-50 rounded-lg">
                                        <div class="text-2xl font-bold text-blue-600">{{ $project->subscriptions_count ?? 0 }}</div>
                                        <div class="text-xs text-blue-600">Subscribers</div>
                                    </div>
                                    <div class="text-center p-3 bg-green-50 rounded-lg">
                                        <div class="text-2xl font-bold text-green-600">{{ $project->active_subscriptions_count ?? 0 }}</div>
                                        <div class="text-xs text-green-600">Active</div>
                                    </div>
                                </div>

                                <!-- Project Meta -->
                                <div class="text-xs text-gray-500 mb-4">
                                    <div>Created: {{ $project->created_at->format('M j, Y') }}</div>
                                    <div>ID: {{ $project->public_id }}</div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex flex-col space-y-2">
                                    <a href="{{ route('projects.show', $project) }}" class="w-full text-center bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded transition-colors duration-200">
                                        View Details
                                    </a>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('projects.edit', $project) }}" class="flex-1 text-center bg-gray-500 hover:bg-gray-600 text-white font-medium py-1 px-3 rounded text-sm transition-colors duration-200">
                                            Edit
                                        </a>
                                        <a href="{{ route('projects.snippet', $project) }}" class="flex-1 text-center bg-green-500 hover:bg-green-600 text-white font-medium py-1 px-3 rounded text-sm transition-colors duration-200">
                                            Snippet
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($projects->hasPages())
                    <div class="mt-8">
                        {{ $projects->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>