<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Website</title>
</head>
<body class="bg-gray-100">

<!-- Header -->
<header class="bg-blue-800 text-white shadow-md">
    <div class="container mx-auto flex justify-between items-center py-4 px-6">
        <!-- Logo -->
        <a href="index.php?ref=logo" class="flex items-center space-x-2">
            <img src="https://www.modernlisim.com/wp-content/uploads/2023/10/M-logo-White.png" alt="Logo" class="h-10"> <!-- Replace 'logo.png' with your actual logo path -->
            <span class="text-xl font-bold">Invoicisim</span>
        </a>

        <!-- Navigation Menu -->
        <nav>
            <ul class="hidden md:flex space-x-6">
                <li><a href="index.php?ref=menu" class="hover:underline">Home</a></li>
                <li><a href="clients.php" class="hover:underline">Clients</a></li>
                <li><a href="invoices.php" class="hover:underline">Invoices</a></li>
                
            </ul>
        </nav>

        <!-- Mobile Menu Button -->
        <button id="menu-toggle" class="md:hidden focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
            </svg>
        </button>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden bg-blue-700 text-white">
        <ul class="space-y-2 py-4 text-center">
            <li><a href="index.php" class="block py-2">Home</a></li>
            <li><a href="about.php" class="block py-2">About</a></li>
            <li><a href="services.php" class="block py-2">Services</a></li>
            <li><a href="contact.php" class="block py-2">Contact</a></li>
        </ul>
    </div>
</header>

<script>
    document.getElementById("menu-toggle").addEventListener("click", function () {
        var menu = document.getElementById("mobile-menu");
        menu.classList.toggle("hidden");
    });
</script>

</body>
</html>
