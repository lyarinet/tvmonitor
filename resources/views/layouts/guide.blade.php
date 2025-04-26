<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - TV Monitor System</title>
    <meta name="description" content="User guides for the TV Monitor System">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            color: #1a202c;
            line-height: 1.6;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-weight: 600;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }
        
        p {
            margin-bottom: 1rem;
        }
        
        .guide-content a {
            color: #4299e1;
            text-decoration: underline;
        }
        
        .guide-content a:hover {
            color: #2b6cb0;
        }
        
        code {
            background-color: #f7fafc;
            padding: 0.2rem 0.4rem;
            border-radius: 0.25rem;
            font-family: 'Menlo', 'Monaco', 'Consolas', monospace;
            font-size: 0.875rem;
            color: #e53e3e;
        }
        
        pre {
            background-color: #f7fafc;
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin-bottom: 1.5rem;
        }
        
        pre code {
            background-color: transparent;
            padding: 0;
            color: #1a202c;
        }
        
        .table-of-contents {
            background-color: #f7fafc;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }
        
        .table-of-contents ul {
            list-style-type: none;
            padding-left: 0;
        }
        
        .table-of-contents li {
            margin-bottom: 0.5rem;
        }
        
        .table-of-contents a {
            text-decoration: none;
            color: #4a5568;
        }
        
        .table-of-contents a:hover {
            color: #2d3748;
            text-decoration: underline;
        }
    </style>
</head>
<body class="bg-gray-50">
    <header class="bg-blue-600 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">TV Monitor System</h1>
                <p class="text-blue-100">Documentation & User Guides</p>
            </div>
            <nav>
                <ul class="flex space-x-6">
                    <li><a href="/admin" class="hover:text-blue-200 transition-colors">Admin Panel</a></li>
                    <li><a href="/guides" class="hover:text-blue-200 transition-colors">All Guides</a></li>
                    <li><a href="/dashboard" class="hover:text-blue-200 transition-colors">Dashboard</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="guide-content py-8">
        @yield('content')
    </main>

    <footer class="bg-gray-800 text-white py-8">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between">
                <div class="mb-6 md:mb-0">
                    <h3 class="text-xl font-semibold mb-4">TV Monitor System</h3>
                    <p class="text-gray-400 max-w-md">
                        A comprehensive solution for monitoring and managing video streams for broadcast environments.
                    </p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="/admin" class="text-gray-400 hover:text-white transition-colors">Admin Panel</a></li>
                        <li><a href="/guides" class="text-gray-400 hover:text-white transition-colors">User Guides</a></li>
                        <li><a href="/dashboard" class="text-gray-400 hover:text-white transition-colors">Dashboard</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} TV Monitor System. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html> 