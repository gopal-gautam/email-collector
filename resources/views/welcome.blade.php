<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Newsletter Collector') }} - Powerful Newsletter Management Platform</title>
    <meta name="description" content="Collect and manage newsletter subscriptions across multiple websites with our secure, API-first platform featuring analytics, double opt-in, and seamless integration.">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        .animate-float-delay {
            animation: float 6s ease-in-out infinite;
            animation-delay: -3s;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .animate-fade-in {
            animation: fadeIn 1s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .blob {
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            animation: blob-anim 20s infinite;
        }
        @keyframes blob-anim {
            0%, 100% { border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; }
            25% { border-radius: 58% 42% 75% 25% / 76% 46% 54% 24%; }
            50% { border-radius: 50% 50% 33% 67% / 55% 27% 73% 45%; }
            75% { border-radius: 33% 67% 58% 42% / 63% 68% 32% 37%; }
        }
    </style>
</head>
<body class="font-inter antialiased bg-white overflow-x-hidden">
    <!-- Navigation -->
    <nav class="fixed w-full z-50 bg-white/80 backdrop-blur-md border-b border-gray-100 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h1 class="text-xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">Newsletter Collector</h1>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ route('projects.index') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200">Login</a>
                        <a href="{{ route('register') }}" class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">Get Started</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative min-h-screen gradient-bg flex items-center justify-center overflow-hidden">
        <!-- Animated background elements -->
        <div class="absolute top-10 left-10 w-72 h-72 bg-white/10 rounded-full blur-xl animate-float"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 bg-purple-400/20 rounded-full blur-xl animate-float-delay"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-white/5 blob"></div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-32 text-center">
            <div class="animate-fade-in">
                <div class="mb-8">
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-white/20 text-white backdrop-blur-sm border border-white/20">
                        🚀 Launch your newsletter management today
                    </span>
                </div>
                <h1 class="text-5xl md:text-7xl font-bold text-white mb-8 leading-tight">
                    Powerful Newsletter<br>
                    <span class="bg-gradient-to-r from-yellow-400 to-pink-400 bg-clip-text text-transparent">Management Platform</span>
                </h1>
                <p class="text-xl md:text-2xl text-white/90 mb-12 max-w-4xl mx-auto leading-relaxed">
                    Collect, manage, and analyze newsletter subscriptions across multiple websites with our secure, API-first platform featuring real-time analytics and seamless integration.
                </p>
                <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
                    @auth
                        <a href="{{ route('projects.index') }}" class="group relative inline-flex items-center justify-center px-10 py-4 text-lg font-semibold text-purple-600 bg-white rounded-2xl hover:bg-gray-50 transition-all duration-300 transform hover:scale-105 shadow-2xl hover:shadow-purple-500/25">
                            <span class="relative z-10">Go to Dashboard</span>
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="group relative inline-flex items-center justify-center px-10 py-4 text-lg font-semibold text-white bg-gradient-to-r from-yellow-400 to-pink-500 rounded-2xl hover:from-yellow-500 hover:to-pink-600 transition-all duration-300 transform hover:scale-105 shadow-2xl hover:shadow-pink-500/25">
                            <span class="relative z-10">Start Free Trial</span>
                            <svg class="w-5 h-5 ml-2 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                        <a href="#features" class="group inline-flex items-center justify-center px-10 py-4 text-lg font-semibold text-white bg-white/10 backdrop-blur-sm border-2 border-white/30 rounded-2xl hover:bg-white/20 transition-all duration-300 transform hover:scale-105">
                            Learn More
                            <svg class="w-5 h-5 ml-2 transition-transform group-hover:translate-y-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                            </svg>
                        </a>
                    @endauth
                </div>
                
                <!-- Statistics -->
                <div class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-8 max-w-3xl mx-auto">
                    <div class="text-center p-6 bg-white/10 backdrop-blur-sm rounded-2xl border border-white/20">
                        <div class="text-3xl font-bold text-white mb-2">99.9%</div>
                        <div class="text-white/80">Uptime Guaranteed</div>
                    </div>
                    <div class="text-center p-6 bg-white/10 backdrop-blur-sm rounded-2xl border border-white/20">
                        <div class="text-3xl font-bold text-white mb-2">10K+</div>
                        <div class="text-white/80">Active Projects</div>
                    </div>
                    <div class="text-center p-6 bg-white/10 backdrop-blur-sm rounded-2xl border border-white/20">
                        <div class="text-3xl font-bold text-white mb-2">50M+</div>
                        <div class="text-white/80">Emails Processed</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div id="features" class="py-32 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20">
                <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800 mb-6">
                    ✨ Feature Rich Platform
                </div>
                <h2 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent mb-6">Everything You Need</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    Built for developers and businesses who need a reliable, scalable newsletter management solution with enterprise-grade features.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="group p-8 bg-white rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100 hover:border-blue-200">
                    <div class="w-16 h-16 bg-gradient-to-r from-green-400 to-blue-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-blue-600 transition-colors duration-300">Double Opt-in Security</h3>
                    <p class="text-gray-600 leading-relaxed">Ensure GDPR compliance with automatic double opt-in confirmation emails and secure subscription management with built-in privacy protection.</p>
                </div>

                <!-- Feature 2 -->
                <div class="group p-8 bg-white rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100 hover:border-purple-200">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-400 to-pink-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-purple-600 transition-colors duration-300">RESTful API</h3>
                    <p class="text-gray-600 leading-relaxed">Integrate seamlessly with any platform using our robust API with authentication, rate limiting, comprehensive documentation, and SDK support.</p>
                </div>

                <!-- Feature 3 -->
                <div class="group p-8 bg-white rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100 hover:border-indigo-200">
                    <div class="w-16 h-16 bg-gradient-to-r from-indigo-400 to-purple-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-indigo-600 transition-colors duration-300">Real-time Analytics</h3>
                    <p class="text-gray-600 leading-relaxed">Track subscription rates, bounce rates, and engagement metrics with detailed analytics, custom reports, and CSV export capabilities.</p>
                </div>

                <!-- Feature 4 -->
                <div class="group p-8 bg-white rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100 hover:border-yellow-200">
                    <div class="w-16 h-16 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-yellow-600 transition-colors duration-300">JavaScript SDK</h3>
                    <p class="text-gray-600 leading-relaxed">Easy integration with any website using our lightweight JavaScript snippet. Just copy, paste, and start collecting with zero configuration.</p>
                </div>

                <!-- Feature 5 -->
                <div class="group p-8 bg-white rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100 hover:border-red-200">
                    <div class="w-16 h-16 bg-gradient-to-r from-red-400 to-pink-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-red-600 transition-colors duration-300">Enterprise Security</h3>
                    <p class="text-gray-600 leading-relaxed">CORS protection, API key authentication, rate limiting, IP masking, and advanced security features ensure your data stays protected.</p>
                </div>

                <!-- Feature 6 -->
                <div class="group p-8 bg-white rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100 hover:border-teal-200">
                    <div class="w-16 h-16 bg-gradient-to-r from-teal-400 to-blue-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-teal-600 transition-colors duration-300">Multi-Project Support</h3>
                    <p class="text-gray-600 leading-relaxed">Manage multiple websites and projects from a single dashboard with isolated data, separate API keys, and custom configurations.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- How It Works Section -->
    <div class="py-32 bg-white relative overflow-hidden">
        <!-- Background decoration -->
        <div class="absolute inset-0 bg-gradient-to-r from-blue-50 to-purple-50 transform -skew-y-1 origin-top-left"></div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20">
                <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gradient-to-r from-blue-100 to-purple-100 text-purple-800 mb-6">
                    🚀 Simple Setup Process
                </div>
                <h2 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent mb-6">How It Works</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    Get started in minutes with our simple three-step process. No complex configuration required.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 relative">
                <!-- Connection lines for desktop -->
                <div class="hidden md:block absolute top-16 left-1/3 w-1/3 h-0.5 bg-gradient-to-r from-blue-300 to-purple-300 transform translate-y-8"></div>
                <div class="hidden md:block absolute top-16 right-1/3 w-1/3 h-0.5 bg-gradient-to-r from-purple-300 to-pink-300 transform translate-y-8"></div>
                
                <div class="relative text-center group">
                    <div class="relative mb-8">
                        <div class="w-24 h-24 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-3xl flex items-center justify-center text-3xl font-bold mx-auto mb-6 shadow-2xl group-hover:scale-110 transition-transform duration-300">
                            1
                        </div>
                        <div class="absolute -inset-4 bg-gradient-to-r from-blue-500 to-purple-600 rounded-3xl opacity-20 blur-xl group-hover:opacity-30 transition-opacity duration-300"></div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Create Your Project</h3>
                    <p class="text-gray-600 leading-relaxed">Sign up and create a new project for your website. Get your unique API key and configure your settings in seconds.</p>
                </div>
                
                <div class="relative text-center group">
                    <div class="relative mb-8">
                        <div class="w-24 h-24 bg-gradient-to-r from-purple-500 to-pink-600 text-white rounded-3xl flex items-center justify-center text-3xl font-bold mx-auto mb-6 shadow-2xl group-hover:scale-110 transition-transform duration-300">
                            2
                        </div>
                        <div class="absolute -inset-4 bg-gradient-to-r from-purple-500 to-pink-600 rounded-3xl opacity-20 blur-xl group-hover:opacity-30 transition-opacity duration-300"></div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Add to Your Website</h3>
                    <p class="text-gray-600 leading-relaxed">Copy our JavaScript snippet and add it to your website, or integrate directly using our comprehensive REST API.</p>
                </div>
                
                <div class="relative text-center group">
                    <div class="relative mb-8">
                        <div class="w-24 h-24 bg-gradient-to-r from-pink-500 to-red-600 text-white rounded-3xl flex items-center justify-center text-3xl font-bold mx-auto mb-6 shadow-2xl group-hover:scale-110 transition-transform duration-300">
                            3
                        </div>
                        <div class="absolute -inset-4 bg-gradient-to-r from-pink-500 to-red-600 rounded-3xl opacity-20 blur-xl group-hover:opacity-30 transition-opacity duration-300"></div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Start Collecting</h3>
                    <p class="text-gray-600 leading-relaxed">Begin collecting subscriptions immediately with automatic confirmation emails and comprehensive real-time analytics.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="relative py-32 bg-gradient-to-r from-gray-900 to-black overflow-hidden">
        <!-- Background decoration -->
        <div class="absolute inset-0">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl"></div>
        </div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="mb-8">
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-white/10 text-white backdrop-blur-sm border border-white/20">
                    🎆 Join Thousands of Satisfied Users
                </span>
            </div>
            <h2 class="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight">
                Ready to Get Started?
            </h2>
            <p class="text-xl md:text-2xl text-gray-300 mb-12 max-w-3xl mx-auto leading-relaxed">
                Join thousands of websites using Newsletter Collector to manage their subscriptions efficiently and securely.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-6 justify-center items-center mb-16">
                @auth
                    <a href="{{ route('projects.index') }}" class="group relative inline-flex items-center justify-center px-12 py-4 text-xl font-bold text-white bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl hover:from-blue-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 shadow-2xl hover:shadow-purple-500/25">
                        <span class="relative z-10">Go to Dashboard</span>
                        <svg class="w-6 h-6 ml-3 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                @else
                    <a href="{{ route('register') }}" class="group relative inline-flex items-center justify-center px-12 py-4 text-xl font-bold text-white bg-gradient-to-r from-yellow-400 to-pink-500 rounded-2xl hover:from-yellow-500 hover:to-pink-600 transition-all duration-300 transform hover:scale-105 shadow-2xl hover:shadow-pink-500/25">
                        <span class="relative z-10">Start Your Free Trial</span>
                        <svg class="w-6 h-6 ml-3 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                @endauth
            </div>
            
            <!-- Trust indicators -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 max-w-4xl mx-auto">
                <div class="flex flex-col items-center p-6 bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10">
                    <div class="text-3xl font-bold text-white mb-2">99.9%</div>
                    <div class="text-gray-400 text-sm">Uptime SLA</div>
                </div>
                <div class="flex flex-col items-center p-6 bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10">
                    <div class="text-3xl font-bold text-white mb-2">24/7</div>
                    <div class="text-gray-400 text-sm">Support Available</div>
                </div>
                <div class="flex flex-col items-center p-6 bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10">
                    <div class="text-3xl font-bold text-white mb-2">GDPR</div>
                    <div class="text-gray-400 text-sm">Compliant</div>
                </div>
                <div class="flex flex-col items-center p-6 bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10">
                    <div class="text-3xl font-bold text-white mb-2">SOC2</div>
                    <div class="text-gray-400 text-sm">Certified</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <div class="md:col-span-2">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold">Newsletter Collector</h3>
                    </div>
                    <p class="text-gray-400 mb-6 max-w-md leading-relaxed">
                        Powerful newsletter management platform built for developers and businesses who need reliable, scalable solutions with enterprise-grade security.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-gray-700 rounded-lg flex items-center justify-center transition-colors duration-200">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-gray-700 rounded-lg flex items-center justify-center transition-colors duration-200">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-gray-700 rounded-lg flex items-center justify-center transition-colors duration-200">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-6 text-white">Features</h4>
                    <ul class="space-y-3 text-gray-400">
                        <li><a href="#features" class="hover:text-white transition-colors duration-200">RESTful API</a></li>
                        <li><a href="#features" class="hover:text-white transition-colors duration-200">Double Opt-in</a></li>
                        <li><a href="#features" class="hover:text-white transition-colors duration-200">Real-time Analytics</a></li>
                        <li><a href="#features" class="hover:text-white transition-colors duration-200">JavaScript SDK</a></li>
                        <li><a href="#features" class="hover:text-white transition-colors duration-200">Enterprise Security</a></li>
                        <li><a href="#features" class="hover:text-white transition-colors duration-200">Multi-Project Support</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-6 text-white">Resources</h4>
                    <ul class="space-y-3 text-gray-400">
                        <li><a href="/api/documentation" class="hover:text-white transition-colors duration-200">API Documentation</a></li>
                        @auth
                            <li><a href="{{ route('projects.index') }}" class="hover:text-white transition-colors duration-200">Dashboard</a></li>
                            <li><a href="{{ route('profile.edit') }}" class="hover:text-white transition-colors duration-200">Profile Settings</a></li>
                        @else
                            <li><a href="{{ route('login') }}" class="hover:text-white transition-colors duration-200">Login</a></li>
                            <li><a href="{{ route('register') }}" class="hover:text-white transition-colors duration-200">Create Account</a></li>
                        @endauth
                        <li><a href="#" class="hover:text-white transition-colors duration-200">Support Center</a></li>
                        <li><a href="#" class="hover:text-white transition-colors duration-200">Status Page</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-12 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-400 text-sm">
                        &copy; {{ date('Y') }} Newsletter Collector. All rights reserved.
                    </p>
                    <div class="flex space-x-6 mt-4 md:mt-0">
                        <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors duration-200">Privacy Policy</a>
                        <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors duration-200">Terms of Service</a>
                        <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors duration-200">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>