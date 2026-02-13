<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Home | Coding Competition</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #121215d0 0%, #96979b 100%);
            min-height: 100vh;
        }

        /* Custom animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                transform: translateX(-20px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }

        .animate-slide-in {
            animation: slideIn 0.5s ease-out forwards;
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .gradient-text {
            background: linear-gradient(135deg, #1d1d25 0%, #96979b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .plain-text {
            color: #090404;
            /* solid color */
            background: none;
            /* remove any background gradients */
            -webkit-background-clip: unset;
            -webkit-text-fill-color: unset;
        }

        .h2 {
            color: white;
        }

        .features h3 {
            color: black;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="sticky top-0 z-50 glass-effect shadow-lg">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo/Brand -->
                <div class="flex items-center space-x-3 animate-slide-in">
                    <div
                        class="w-10 h-10 rounded-lg bg-gradient-to-r from-black to-gray-600 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold plain-text">The Hunger Games</h1>
                        <p class="text-xs text-gray-500 plain-text">Coding Competition</p>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-8 animate-slide-in" style="animation-delay: 0.1s">
                    <a href="#home"
                        class="text-gray-700 hover:text-gray-600 font-medium transition duration-200 hover:-translate-y-0.5">Home</a>
                    <a href="#about"
                        class="text-gray-700 hover:text-gray-600 font-medium transition duration-200 hover:-translate-y-0.5">About</a>
                    <a href="{{ url('login') }}"
                        class="px-6 py-2 bg-gradient-to-r from-black to-gray-600 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition duration-200 transform hover:-translate-y-0.5 hover:from-gray-700 hover:to-black">
                        Login
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobileMenuBtn" class="md:hidden text-gray-700">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div id="mobileMenu" class="hidden md:hidden mt-4 space-y-3 animate-fade-in">
                <a href="#home"
                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition duration-200">Home</a>
                <a href="#about"
                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition duration-200">About</a>
                <a href="{{ url('login') }}"
                    class="block px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-medium rounded-lg text-center">Login</a>
            </div>
        </div>
    </nav>


    <!-- Hero Content -->
    <div class="container mx-auto max-w-7xl px-8 md:px-12 py-12">
        <!-- Hero Section -->
        <section id="home" class="mb-16 animate-fade-in">
            <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-3xl overflow-hidden shadow-2xl">

                <div class="grid md:grid-cols-2 gap-12 items-center">

                    <!-- LEFT: Hero Content -->
                    <div class="p-10 md:p-16">
                        <div
                            class="inline-flex items-center px-4 py-2 rounded-full bg-gradient-to-r from-black to-gray-500 text-white font-medium mb-6 animate-slide-in">
                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Annual Coding Competition
                        </div>

                        <h1 class="text-5xl md:text-6xl font-bold mb-6 gradient-text animate-slide-in"
                            style="animation-delay: 0.1s">
                            Code. Compete. Conquer.
                        </h1>

                        <p class="text-gray-800 text-lg mb-8 animate-slide-in" style="animation-delay: 0.2s">
                            Join the ultimate coding battlefield where developers showcase their skills,
                            solve real-world challenges, and claim victory in The Hunger Games of programming.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4 animate-slide-in" style="animation-delay: 0.3s">
                            <a href="{{ url('login') }}"
                                class="px-8 py-4 bg-gradient-to-r from-black to-gray-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition duration-200 transform hover:-translate-y-1">
                                Join Competition
                            </a>
                            <a href="#about"
                                class="px-8 py-4 bg-white text-gray-700 font-semibold rounded-xl shadow-md hover:shadow-lg transition duration-200 transform hover:-translate-y-1 border border-gray-200">
                                Learn More
                            </a>
                        </div>
                    </div>

                    <div class="relative p-8 md:p-12 flex justify-center">
                        <div class="relative animate-float max-w-md w-full">

                            <!-- floating blobs -->
                            <div
                                class="absolute -top-6 -left-6 w-24 h-24 bg-gradient-to-r from-black to-gray-600 rounded-2xl opacity-20 animate-pulse">
                            </div>
                            <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-gradient-to-r from-black to-gray-600 rounded-2xl opacity-20 animate-pulse"
                                style="animation-delay: 1s">
                            </div>

                            <!-- code card -->
                            <div class="relative bg-white rounded-2xl shadow-2xl overflow-hidden">
                                <div class="bg-gradient-to-r from-black to-gray-600 p-6 text-white">
                                    <div class="flex items-center justify-between">
                                        <h3 class="font-bold text-lg">Live Coding Arena</h3>
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                                            <span class="text-sm">Live</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-6 bg-gray-900 text-green-400 font-mono">
                                    <div class="text-sm space-y-1">
                                        <div>
                                            <span class="text-purple-400">function</span>
                                            <span class="text-blue-400">solveChallenge</span>() &#123;
                                        </div>
                                        <div class="ml-4">
                                            <span class="text-yellow-400">return</span>
                                            <span class="text-green-400">"Victory"</span>;
                                        </div>
                                        <div>&#125;</div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
    </div>
    </div>
    </section>

    <!-- About Section -->
    <section id="about" class="mb-16 animate-fade-in" style="animation-delay: 0.2s">
        <div class="max-w-7xl mx-auto px-6 lg:px-12">
            <div class="text-center mb-12">
                <h2 class="h2 text-4xl font-bold mb-4">Why Compete With Us?</h2>
                <p class="text-white max-w-2xl mx-auto">Experience the thrill of competitive programming with
                    cutting-edge challenges and a vibrant community.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 mb-12">
                <!-- Feature 1 -->
                <div
                    class="bg-white rounded-2xl shadow-lg p-8 transition duration-300 transform hover:-translate-y-2 hover:shadow-xl">
                    <div
                        class="w-16 h-16 rounded-xl bg-gradient-to-r from-gray-600 to-gray-400 flex items-center justify-center mb-6 mx-auto">
                        <svg class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <section class="features">
                        <h3 class="text-xl font-bold mb-4 text-center">Real-time Challenges</h3>
                    </section>
                    <p class="text-gray-800 text-center">Tackle live coding problems with increasing difficulty levels
                        and real-time leaderboard updates.</p>
                </div>

                <!-- Feature 2 -->
                <div
                    class="bg-white rounded-2xl shadow-lg p-8 transition duration-300 transform hover:-translate-y-2 hover:shadow-xl">
                    <div
                        class="w-16 h-16 rounded-xl bg-gradient-to-r from-gray-600 to-gray-400 flex items-center justify-center mb-6 mx-auto">
                        <svg class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <section class="features">
                        <h3 class="text-xl font-bold mb-4 text-center">Secure Platform</h3>
                    </section>
                    <p class="text-gray-800 text-center">Compete with confidence knowing our platform ensures fair play
                        and protects your intellectual property.</p>
                </div>

                <!-- Feature 3 -->
                <div
                    class="bg-white rounded-2xl shadow-lg p-8 transition duration-300 transform hover:-translate-y-2 hover:shadow-xl">
                    <div
                        class="w-16 h-16 rounded-xl bg-gradient-to-r from-gray-600 to-gray-400 flex items-center justify-center mb-6 mx-auto">
                        <svg class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <section class="features">
                        <h3 class="text-xl font-bold mb-4 text-center">Community Network</h3>
                    </section>
                    <p class="text-gray-800 text-center">Connect with fellow developers, share solutions, and learn from
                        the best in the programming community.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <div class="px-4 sm:px-6 lg:px-8">
        <footer class="bg-gradient-to-r from-gray-900 to-black text-white rounded-3xl overflow-hidden shadow-2xl">
            <div class="p-12">
                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Brand Info -->
                    <div class="animate-slide-in">
                        <div class="flex items-center space-x-3 mb-6">
                            <div
                                class="w-12 h-12 rounded-xl bg-gradient-to-r from-black to-gray-800 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold">The Hunger Games</h3>
                                <p class="text-gray-400 text-sm">Coding Competition</p>
                            </div>
                        </div>
                        <p class="text-gray-400 mb-6">
                            ACLC Programmers Guild.
                            <br />
                            Providing reliable tech since 2022
                        </p>
                        <p class="text-gray-500 text-sm">Copyright © {{ date('Y') }} - All rights reserved</p>
                    </div>

                    <!-- Quick Links -->
                    <div class="animate-slide-in" style="animation-delay: 0.1s">
                        <h4 class="text-lg font-bold mb-6">Quick Links</h4>
                        <ul class="space-y-3">
                            <li><a href="#home" class="text-gray-400 hover:text-white transition duration-200">Home</a>
                            </li>
                            <li><a href="#about"
                                    class="text-gray-400 hover:text-white transition duration-200">About</a>
                            </li>
                            <li><a href="{{ url('login') }}"
                                    class="text-gray-400 hover:text-white transition duration-200">Login</a></li>
                        </ul>
                    </div>

                    <!-- Social Media -->
                    <div class="animate-slide-in" style="animation-delay: 0.2s">
                        <h4 class="text-lg font-bold mb-6">Connect With Us</h4>
                        <div class="flex space-x-4">
                            <a href="#"
                                class="w-12 h-12 rounded-full bg-gray-800 hover:bg-blue-600 flex items-center justify-center transition duration-300 transform hover:-translate-y-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24"
                                    class="fill-current">
                                    <path
                                        d="M15 8.8V6.9c0-.6.4-.9 1-.9h2V3h-2.8c-2.8 0-4.2 1.7-4.2 4.1v1.7H9v3h2v9h3v-9h2.7l.4-3H14z" />
                                </svg>




                            </a>
                            <a href="/credits" class="w-12 h-12 rounded-full bg-gray-800 hover:bg-gray-600
           flex items-center justify-center transition duration-300
           transform hover:-translate-y-1">
                                <img src="{{ asset('images/officialLogo.png') }}" class="w-12 h-12 object-contain" />
                            </a>
                        </div>
                        <p class="text-gray-400 mt-6">Follow us to know more about the ACLC Programmers Guild.</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Mobile menu toggle
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');

            mobileMenuBtn.addEventListener('click', function () {
                mobileMenu.classList.toggle('hidden');
            });

            // Close mobile menu when clicking outside
            document.addEventListener('click', function (event) {
                if (!mobileMenuBtn.contains(event.target) && !mobileMenu.contains(event.target)) {
                    mobileMenu.classList.add('hidden');
                }
            });

            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;

                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        // Close mobile menu if open
                        mobileMenu.classList.add('hidden');

                        window.scrollTo({
                            top: targetElement.offsetTop - 80,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Add animation on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-fade-in');
                    }
                });
            }, observerOptions);

            // Observe all sections for animation
            document.querySelectorAll('section').forEach(section => {
                observer.observe(section);
            });
        });
    </script>
</body>

</html>