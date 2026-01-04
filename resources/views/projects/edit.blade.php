<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Project') }}
        </h2>
    </x-slot>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="mb-6">
                    <a href="{{ route('projects.show', $project) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        ← Back to Project
                    </a>
                </div>

                <form method="POST" action="{{ route('projects.update', $project) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Project Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Project Name</label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $project->name) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="status" 
                                    name="status"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="active" {{ old('status', $project->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $project->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Allowed Origins -->
                        <div>
                            <label for="allowed_origins" class="block text-sm font-medium text-gray-700 mb-2">
                                Allowed Origins
                                <span class="text-sm text-gray-500">(one per line, leave blank for any origin)</span>
                            </label>
                            <textarea id="allowed_origins" 
                                      name="allowed_origins" 
                                      rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="https://example.com&#10;https://www.example.com&#10;*.example.com">{{ old('allowed_origins', is_array($project->allowed_origins) ? implode("\n", $project->allowed_origins) : '') }}</textarea>
                            @error('allowed_origins')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">
                                Enter allowed domains for CORS. Use * for wildcards. Leave empty to allow all origins.
                            </p>
                        </div>

                        <!-- Settings -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900">Email Settings</h3>
                            
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="double_opt_in" 
                                       name="double_opt_in" 
                                       value="1"
                                       {{ old('double_opt_in', $project->double_opt_in) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="double_opt_in" class="ml-2 block text-sm text-gray-700">
                                    Enable double opt-in (subscribers must confirm their email)
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="welcome_email" 
                                       name="welcome_email" 
                                       value="1"
                                       {{ old('welcome_email', $project->welcome_email) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="welcome_email" class="ml-2 block text-sm text-gray-700">
                                    Send welcome email to confirmed subscribers
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="admin_notifications" 
                                       name="admin_notifications" 
                                       value="1"
                                       {{ old('admin_notifications', $project->admin_notifications) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="admin_notifications" class="ml-2 block text-sm text-gray-700">
                                    Send admin notifications for new subscriptions
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-between">
                            <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline">
                                Update Project
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</x-app-layout>