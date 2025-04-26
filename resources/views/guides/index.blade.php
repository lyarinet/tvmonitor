@extends('layouts.guide')

@section('title', 'User Guides')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-8">TV Monitor System User Guides</h1>
    
    <p class="text-lg text-gray-600 mb-8 max-w-3xl">
        Welcome to the TV Monitor System documentation. These guides will help you understand how to use and configure
        the various features of the system to monitor and manage your video streams effectively.
    </p>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($guides as $guide)
            <a href="{{ route('guides.show', $guide['slug']) }}" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="bg-blue-100 text-blue-600 p-3 rounded-full mr-4">
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800">{{ $guide['title'] }}</h2>
                    </div>
                    <p class="text-gray-600">{{ $guide['description'] }}</p>
                    <div class="mt-4 flex justify-end">
                        <span class="text-blue-600 font-medium flex items-center">
                            Read Guide
                            <svg class="w-4 h-4 ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
    
    <div class="mt-12 bg-gray-100 rounded-lg p-6">
        <h2 class="text-2xl font-semibold mb-4">Need Additional Help?</h2>
        <p class="mb-4">
            If you can't find the information you need in these guides, there are several ways to get additional support:
        </p>
        <ul class="list-disc pl-6 space-y-2">
            <li>Check the <a href="#" class="text-blue-600 hover:underline">Frequently Asked Questions</a> section</li>
            <li>Contact your system administrator for assistance</li>
            <li>Refer to the <a href="#" class="text-blue-600 hover:underline">API Documentation</a> for integration information</li>
            <li>Submit a support ticket through the <a href="#" class="text-blue-600 hover:underline">Help Desk</a></li>
        </ul>
    </div>
</div>
@endsection 