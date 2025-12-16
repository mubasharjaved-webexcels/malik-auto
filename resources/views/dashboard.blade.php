
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - {{ config('app.name') }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-900">Dashboard</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Hello, {{ Auth::user()->name }}!</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Welcome Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Welcome to your Dashboard!</h2>
                    <p class="text-gray-600 mb-4">You are logged in successfully.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                        <!-- User Info Card -->
                        <div class="bg-blue-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-blue-900 mb-2">Your Profile</h3>
                            <p class="text-blue-700"><strong>Name:</strong> {{ Auth::user()->name }}</p>
                            <p class="text-blue-700"><strong>Email:</strong> {{ Auth::user()->email }}</p>
                            <p class="text-blue-700"><strong>Member since:</strong> {{ Auth::user()->created_at->format('M d, Y') }}</p>
                        </div>

                        <!-- Quick Stats Card -->
                        <div class="bg-green-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-green-900 mb-2">Quick Stats</h3>
                            <p class="text-green-700">Last login: {{ now()->format('M d, Y H:i') }}</p>
                            <p class="text-green-700">Status: Active</p>
                            <p class="text-green-700">Account Type: User</p>
                        </div>

                        <!-- Actions Card -->
                        <div class="bg-purple-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-900 mb-2">Quick Actions</h3>
                            <div class="space-y-2">
                                <button class="block w-full text-left text-purple-700 hover:text-purple-900">Edit Profile</button>
                                <button class="block w-full text-left text-purple-700 hover:text-purple-900">Settings</button>
                                <button class="block w-full text-left text-purple-700 hover:text-purple-900">Help & Support</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>