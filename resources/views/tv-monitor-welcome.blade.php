<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>TV Monitor System</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <!-- Styles -->
        <style>
            body {
                font-family: 'Instrument Sans', sans-serif;
                background-color: #030712;
                scroll-behavior: smooth;
            }
            
            .gradient-bg {
                background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            }
            
            .hero-gradient {
                background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
                position: relative;
                overflow: hidden;
            }
            
            .hero-gradient::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%232563eb' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
                opacity: 0.5;
            }
            
            .feature-card {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                border: 1px solid rgba(229, 231, 235, 0.5);
                backdrop-filter: blur(10px);
            }
            
            .feature-card:hover {
                transform: translateY(-8px);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                border-color: #3b82f6;
            }
            
            .feature-icon {
                transition: all 0.3s ease;
            }
            
            .feature-card:hover .feature-icon {
                transform: scale(1.1);
                background-color: #3b82f6;
                color: white;
            }
            
            .btn-primary {
                background-color: #3b82f6;
                color: white;
                padding: 0.75rem 1.5rem;
                border-radius: 0.5rem;
                font-weight: 500;
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            }
            
            .btn-primary:hover {
                background-color: #2563eb;
                transform: translateY(-2px);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            }
            
            .btn-primary:active {
                transform: translateY(0);
            }
            
            .btn-secondary {
                background-color: rgba(255, 255, 255, 0.1);
                color: #3b82f6;
                padding: 0.75rem 1.5rem;
                border-radius: 0.5rem;
                font-weight: 500;
                border: 2px solid #3b82f6;
                transition: all 0.3s ease;
                backdrop-filter: blur(4px);
            }
            
            .btn-secondary:hover {
                background-color: #3b82f6;
                color: white;
                transform: translateY(-2px);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            }
            
            .btn-secondary:active {
                transform: translateY(0);
            }
            
            .nav-link {
                position: relative;
                transition: color 0.3s ease;
            }
            
            .nav-link::after {
                content: '';
                position: absolute;
                width: 0;
                height: 2px;
                bottom: -4px;
                left: 0;
                background-color: #60a5fa;
                transition: width 0.3s ease;
            }
            
            .nav-link:hover::after {
                width: 100%;
            }
            
            .quick-access-card {
                transition: all 0.3s ease;
                border: 1px solid rgba(229, 231, 235, 0.5);
            }
            
            .quick-access-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                border-color: #3b82f6;
            }
            
            .quick-access-icon {
                transition: all 0.3s ease;
            }
            
            .quick-access-card:hover .quick-access-icon {
                transform: scale(1.1);
                background-color: #3b82f6;
                color: white;
            }
            
            .target-audience-item {
                transition: all 0.3s ease;
                padding: 1rem;
                border-radius: 0.5rem;
            }
            
            .target-audience-item:hover {
                background-color: #f0f7ff;
                transform: translateX(10px);
            }
            
            @keyframes float {
                0% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
                100% { transform: translateY(0px); }
            }
            
            .floating-image {
                animation: float 6s ease-in-out infinite;
            }
            
            .scroll-fade {
                opacity: 0;
                transform: translateY(20px);
                transition: all 0.6s ease-out;
            }
            
            .scroll-fade.visible {
                opacity: 1;
                transform: translateY(0);
            }
        </style>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Intersection Observer for scroll animations
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('visible');
                        }
                    });
                }, {
                    threshold: 0.1
                });
                
                document.querySelectorAll('.scroll-fade').forEach((el) => observer.observe(el));
            });
        </script>
    </head>
    <body class="antialiased">
        <header class="gradient-bg text-white shadow-lg fixed w-full z-50">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold">TV Monitor System</h1>
                        <p class="text-blue-100 text-sm">Professional Broadcast Monitoring Solution</p>
                    </div>
                    <nav>
                        <ul class="flex space-x-8">
                            @if (Route::has('login'))
                                @auth
                                    <li><a href="{{ url('/dashboard') }}" class="nav-link">Dashboard</a></li>
                                @else
                                    <li><a href="{{ route('login') }}" class="nav-link">Log in</a></li>

                                    @if (Route::has('register'))
                                        <li><a href="{{ route('register') }}" class="nav-link">Register</a></li>
                                    @endif
                                @endauth
                            @endif
                            <li><a href="/guides" class="nav-link">Guides</a></li>
                            <li><a href="/admin" class="nav-link">Admin</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </header>

        <main class="pt-24">
            <!-- Hero Section -->
            <section class="hero-gradient text-white py-32">
                <div class="container mx-auto px-4">
                    <div class="flex flex-col md:flex-row items-center max-w-6xl mx-auto">
                        <div class="md:w-1/2 mb-10 md:mb-0 z-10">
                            <h2 class="text-5xl font-bold mb-6 leading-tight">Professional Broadcast Stream Monitoring</h2>
                            <p class="text-xl mb-8 text-blue-100">Monitor, manage, and control your broadcast streams with our comprehensive solution. Perfect for TV stations and media production environments.</p>
                            <div class="flex flex-wrap gap-4">
                                <a href="/view/streams" class="btn-primary">
                                    View Streams
                                    <span class="ml-2">→</span>
                                </a>
                                <a href="/guides" class="btn-secondary">Read Guides</a>
                            </div>
                        </div>
                        <div class="md:w-1/2 z-10">
                            <div class="w-full aspect-video bg-gradient-to-br from-blue-600 to-blue-800 rounded-lg shadow-2xl p-4 floating-image">
                                <div class="w-full h-full grid grid-cols-3 grid-rows-3 gap-2">
                                    <!-- Row 1 -->
                                    <div class="bg-black/40 rounded border border-white/20 backdrop-blur relative group overflow-hidden">
                                        <div class="absolute inset-0 bg-[url('https://picsum.photos/seed/1/300/200')] bg-cover bg-center opacity-75"></div>
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                        <div class="absolute bottom-2 left-2 text-white/90 text-xs font-medium">Channel 1</div>
                                        <div class="absolute top-2 right-2 px-1.5 py-0.5 bg-blue-500/70 rounded text-[10px] text-white/90">Live</div>
                                    </div>
                                    <div class="bg-black/40 rounded border border-white/20 backdrop-blur relative group overflow-hidden">
                                        <div class="absolute inset-0 bg-[url('https://picsum.photos/seed/2/300/200')] bg-cover bg-center opacity-75"></div>
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                        <div class="absolute bottom-2 left-2 text-white/90 text-xs font-medium">Channel 2</div>
                                        <div class="absolute top-2 right-2 px-1.5 py-0.5 bg-green-500/70 rounded text-[10px] text-white/90">HD</div>
                                    </div>
                                    <div class="bg-black/40 rounded border border-white/20 backdrop-blur relative group overflow-hidden">
                                        <div class="absolute inset-0 bg-[url('https://picsum.photos/seed/3/300/200')] bg-cover bg-center opacity-75"></div>
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                        <div class="absolute bottom-2 left-2 text-white/90 text-xs font-medium">Channel 3</div>
                                        <div class="absolute top-2 right-2 px-1.5 py-0.5 bg-blue-500/70 rounded text-[10px] text-white/90">Live</div>
                                    </div>
                                    
                                    <!-- Row 2 -->
                                    <div class="bg-black/40 rounded border border-white/20 backdrop-blur relative group overflow-hidden">
                                        <div class="absolute inset-0 bg-[url('https://picsum.photos/seed/4/300/200')] bg-cover bg-center opacity-75"></div>
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                        <div class="absolute bottom-2 left-2 text-white/90 text-xs font-medium">Sports</div>
                                        <div class="absolute top-2 right-2 px-1.5 py-0.5 bg-yellow-500/70 rounded text-[10px] text-white/90">4K</div>
                                    </div>
                                    <div class="bg-black/40 rounded border border-red-500/50 backdrop-blur relative group overflow-hidden">
                                        <div class="absolute inset-0 bg-[url('https://picsum.photos/seed/5/300/200')] bg-cover bg-center opacity-75"></div>
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                        <div class="absolute bottom-2 left-2 text-white/90 text-xs font-medium">Program Out</div>
                                        <div class="absolute top-2 right-2 px-1.5 py-0.5 bg-red-500/70 rounded text-[10px] text-white/90">REC</div>
                                        <div class="absolute top-2 left-2 w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                                    </div>
                                    <div class="bg-black/40 rounded border border-white/20 backdrop-blur relative group overflow-hidden">
                                        <div class="absolute inset-0 bg-[url('https://picsum.photos/seed/6/300/200')] bg-cover bg-center opacity-75"></div>
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                        <div class="absolute bottom-2 left-2 text-white/90 text-xs font-medium">News Feed</div>
                                        <div class="absolute top-2 right-2 px-1.5 py-0.5 bg-blue-500/70 rounded text-[10px] text-white/90">Live</div>
                                    </div>
                                    
                                    <!-- Row 3 -->
                                    <div class="bg-black/40 rounded border border-white/20 backdrop-blur relative group overflow-hidden">
                                        <div class="absolute inset-0 bg-[url('https://picsum.photos/seed/7/300/200')] bg-cover bg-center opacity-75"></div>
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                        <div class="absolute bottom-2 left-2 text-white/90 text-xs font-medium">Weather</div>
                                        <div class="absolute top-2 right-2 px-1.5 py-0.5 bg-purple-500/70 rounded text-[10px] text-white/90">GFX</div>
                                    </div>
                                    <div class="bg-black/40 rounded border border-white/20 backdrop-blur relative group overflow-hidden">
                                        <div class="absolute inset-0 bg-[url('https://picsum.photos/seed/8/300/200')] bg-cover bg-center opacity-75"></div>
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                        <div class="absolute bottom-2 left-2 text-white/90 text-xs font-medium">Studio A</div>
                                        <div class="absolute top-2 right-2 px-1.5 py-0.5 bg-blue-500/70 rounded text-[10px] text-white/90">Live</div>
                                    </div>
                                    <div class="bg-black/40 rounded border border-white/20 backdrop-blur relative group overflow-hidden">
                                        <div class="absolute inset-0 bg-[url('https://picsum.photos/seed/9/300/200')] bg-cover bg-center opacity-75"></div>
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                        <div class="absolute bottom-2 left-2 text-white/90 text-xs font-medium">Preview</div>
                                        <div class="absolute top-2 right-2 px-1.5 py-0.5 bg-orange-500/70 rounded text-[10px] text-white/90">PVW</div>
                                    </div>
                                    
                                    <!-- Status Indicators -->
                                    <div class="absolute bottom-2 right-2 flex space-x-2">
                                        <div class="px-2 py-1 bg-black/60 rounded text-xs text-white/90 backdrop-blur flex items-center">
                                            <div class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5 animate-pulse"></div>
                                            Live
                                        </div>
                                        <div class="px-2 py-1 bg-black/60 rounded text-xs text-white/90 backdrop-blur">3x3 Grid</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section class="py-32 bg-gradient-to-b from-white to-gray-50">
                <div class="container mx-auto px-4">
                    <div class="text-center max-w-3xl mx-auto mb-16 scroll-fade">
                        <h2 class="text-4xl font-bold mb-6">Key Features</h2>
                        <p class="text-gray-600 text-lg">Powerful tools and capabilities designed for professional broadcast environments</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-7xl mx-auto">
                        <!-- Feature cards with updated styles -->
                        <div class="feature-card bg-white/80 p-8 rounded-xl shadow-lg scroll-fade">
                            <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mb-6 feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold mb-3">Multi-Screen Support</h3>
                            <p class="text-gray-600">Display a customizable grid of video feeds on a single screen, or across multiple displays.</p>
                        </div>
                        
                        <!-- Repeat for other feature cards with similar styling -->
                        <div class="feature-card bg-white/80 p-8 rounded-xl shadow-lg scroll-fade">
                            <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mb-6 feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold mb-3">Customizable Layouts</h3>
                            <p class="text-gray-600">Create and save custom layout configurations with various grid sizes and aspect ratios.</p>
                        </div>
                        <div class="feature-card bg-white/80 p-8 rounded-xl shadow-lg scroll-fade">
                            <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mb-6 feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold mb-3">Real-time Monitoring</h3>
                            <p class="text-gray-600">Experience low-latency video and audio monitoring for critical broadcast applications.</p>
                        </div>
                        <div class="feature-card bg-white/80 p-8 rounded-xl shadow-lg scroll-fade">
                            <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mb-6 feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold mb-3">Signal Analysis</h3>
                            <p class="text-gray-600">Access detailed signal analysis tools, including waveform displays, audio metering, and video quality indicators.</p>
                        </div>
                        <div class="feature-card bg-white/80 p-8 rounded-xl shadow-lg scroll-fade">
                            <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mb-6 feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold mb-3">Alerting</h3>
                            <p class="text-gray-600">Configure customizable alerts and notifications for signal loss, audio levels, and other critical events.</p>
                        </div>
                        <div class="feature-card bg-white/80 p-8 rounded-xl shadow-lg scroll-fade">
                            <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mb-6 feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold mb-3">User Management</h3>
                            <p class="text-gray-600">Manage user access and permissions, with support for multiple user roles and authentication methods.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Quick Access Section -->
            <section class="py-24 bg-gray-900 text-white">
                <div class="container mx-auto px-4">
                    <div class="text-center mb-16 scroll-fade">
                        <h2 class="ai-heading text-4xl font-bold mb-4 font-['Space_Grotesk']">Quick Access</h2>
                        <p class="text-blue-200/80 text-lg">Everything you need, right at your fingertips</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 max-w-7xl mx-auto">
                        <!-- Quick access cards with glass morphism effect -->
                        <a href="/view/streams" class="feature-card p-6">
                            <div class="flex items-center mb-4">
                                <div class="feature-icon w-12 h-12 rounded-xl flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-white font-['Space_Grotesk']">View Streams</h3>
                            </div>
                            <p class="text-gray-400">Access all available streams and multiview layouts</p>
                        </a>
                        
                        <!-- Repeat for other quick access cards -->
                        <a href="/admin" class="quick-access-card bg-white/10 backdrop-blur-lg p-6 rounded-xl">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center mr-4 quick-access-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold">Admin Panel</h3>
                            </div>
                            <p class="text-gray-400">Configure streams, manage users, and system settings</p>
                        </a>
                        <a href="/guides" class="quick-access-card bg-white/10 backdrop-blur-lg p-6 rounded-xl">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center mr-4 quick-access-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold">User Guides</h3>
                            </div>
                            <p class="text-gray-400">Access comprehensive documentation and user guides</p>
                        </a>
                        <a href="/dashboard" class="quick-access-card bg-white/10 backdrop-blur-lg p-6 rounded-xl">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center mr-4 quick-access-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold">Dashboard</h3>
                            </div>
                            <p class="text-gray-400">View system status and key performance indicators</p>
                        </a>
                    </div>
                </div>
            </section>
            
            <!-- Documentation Section -->
            <section class="py-32 bg-gradient-to-b from-gray-50 to-white">
                <div class="container mx-auto px-4">
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden max-w-6xl mx-auto scroll-fade">
                        <div class="flex flex-col md:flex-row">
                            <div class="md:w-1/2 p-12">
                                <h2 class="text-4xl font-bold mb-6">Comprehensive Documentation</h2>
                                <p class="text-gray-600 mb-8 text-lg">Our documentation covers everything you need to know about the TV Monitor System, from basic setup to advanced configurations.</p>
                                <div class="mb-6">
                                    <h4 class="font-semibold mb-2">Available Guides:</h4>
                                    <ul class="space-y-2 text-gray-600">
                                        <li class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            <span>Input Stream Configuration Guide</span>
                                        </li>
                                        <li class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            <span>Demo Data and Multiview Layout Guide</span>
                                        </li>
                                        <li class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            <span>Admin Interface User Manual</span>
                                        </li>
                                        <li class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            <span>Troubleshooting and FAQ</span>
                                        </li>
                                    </ul>
                                </div>
                                <a href="/guides" class="btn-primary inline-block">View All Guides</a>
                            </div>
                            <div class="md:w-1/2 bg-gray-50 p-12">
                                <h3 class="text-xl font-semibold mb-4">Getting Started</h3>
                                <p class="text-gray-600 mb-4">New to the TV Monitor System? Follow these steps to get started:</p>
                                <ol class="list-decimal list-inside space-y-2 text-gray-600 mb-6">
                                    <li>Log in to the admin panel</li>
                                    <li>Configure your first input stream</li>
                                    <li>Create a multiview layout</li>
                                    <li>Start monitoring your streams</li>
                                </ol>
                                <p class="text-gray-600">Check our <a href="/guides" class="text-blue-600 hover:underline">comprehensive guides</a> for detailed instructions on each step.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Target Audience Section -->
            <section class="py-32 bg-gradient-to-b from-gray-900 to-blue-900 relative overflow-hidden">
                <!-- Background decoration -->
                <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\"20\" height=\"20\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cpath d=\"M0 0h20v20H0z\" fill=\"none\"/%3E%3Cpath d=\"M12.5 10a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z\" fill=\"rgba(59,130,246,0.05)\"/%3E%3C/svg%3E')] opacity-30"></div>
                
                <div class="container mx-auto px-4 relative">
                    <div class="max-w-5xl mx-auto scroll-fade">
                        <div class="text-center mb-16">
                            <h2 class="text-4xl font-bold text-white mb-4">Target Audience</h2>
                            <p class="text-xl text-blue-200">This multiviewer is ideal for:</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Audience Item 1 -->
                            <div class="target-audience-card bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10 hover:border-blue-400/50 transition-all duration-300 group">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg text-white group-hover:text-blue-300 transition-colors duration-300">Broadcast Studios</h3>
                                </div>
                                <p class="mt-4 text-blue-200/80 pl-16">Professional production facilities and television studios</p>
                            </div>

                            <!-- Audience Item 2 -->
                            <div class="target-audience-card bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10 hover:border-blue-400/50 transition-all duration-300 group">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg text-white group-hover:text-blue-300 transition-colors duration-300">Control Rooms</h3>
                                </div>
                                <p class="mt-4 text-blue-200/80 pl-16">Master control and network operations centers</p>
                            </div>

                            <!-- Audience Item 3 -->
                            <div class="target-audience-card bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10 hover:border-blue-400/50 transition-all duration-300 group">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16l2.879-2.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg text-white group-hover:text-blue-300 transition-colors duration-300">OB Vans</h3>
                                </div>
                                <p class="mt-4 text-blue-200/80 pl-16">Outside broadcast and mobile production units</p>
                            </div>

                            <!-- Audience Item 4 -->
                            <div class="target-audience-card bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10 hover:border-blue-400/50 transition-all duration-300 group">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg text-white group-hover:text-blue-300 transition-colors duration-300">Post-Production</h3>
                                </div>
                                <p class="mt-4 text-blue-200/80 pl-16">Professional editing and post-production suites</p>
                            </div>

                            <!-- Audience Item 5 -->
                            <div class="target-audience-card bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10 hover:border-blue-400/50 transition-all duration-300 group">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg text-white group-hover:text-blue-300 transition-colors duration-300">Command Centers</h3>
                                </div>
                                <p class="mt-4 text-blue-200/80 pl-16">Government and military command facilities</p>
                            </div>

                            <!-- Audience Item 6 -->
                            <div class="target-audience-card bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10 hover:border-blue-400/50 transition-all duration-300 group">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg text-white group-hover:text-blue-300 transition-colors duration-300">Surveillance</h3>
                                </div>
                                <p class="mt-4 text-blue-200/80 pl-16">Security and surveillance monitoring facilities</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="bg-gray-950 text-white py-20">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between max-w-6xl mx-auto">
                    <div class="mb-12 md:mb-0">
                        <h3 class="text-2xl font-bold mb-6 font-['Space_Grotesk']">TV Monitor AI</h3>
                        <p class="text-blue-200/80 max-w-md text-lg">
                            Next-generation broadcast monitoring powered by artificial intelligence.
                        </p>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-12">
                        <div>
                            <h4 class="text-lg font-semibold mb-4 font-['Space_Grotesk']">Quick Links</h4>
                            <ul class="space-y-2">
                                <li><a href="/view/streams" class="text-blue-200/80 hover:text-white transition-colors">Stream Monitor</a></li>
                                <li><a href="/admin" class="text-blue-200/80 hover:text-white transition-colors">AI Analytics</a></li>
                                <li><a href="/guides" class="text-blue-200/80 hover:text-white transition-colors">Documentation</a></li>
                                <li><a href="/dashboard" class="text-blue-200/80 hover:text-white transition-colors">System Status</a></li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold mb-4 font-['Space_Grotesk']">Resources</h4>
                            <ul class="space-y-2">
                                <li><a href="/guides" class="text-blue-200/80 hover:text-white transition-colors">AI Guides</a></li>
                                <li><a href="#" class="text-blue-200/80 hover:text-white transition-colors">API Reference</a></li>
                                <li><a href="#" class="text-blue-200/80 hover:text-white transition-colors">Neural Training</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="border-t border-blue-900/30 mt-16 pt-8 text-center text-blue-200/60">
                    <p>&copy; {{ date('Y') }} TV Monitor AI. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </body>
</html> 