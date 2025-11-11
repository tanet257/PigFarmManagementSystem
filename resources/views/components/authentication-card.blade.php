<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 relative bg-gray-100">
    <!-- Background overlay แยก layer -->
    <div class="absolute inset-0">
        <img src="https://res.cloudinary.com/dpyfsogka/image/upload/v1762488190/adorable-baby-pigs-farm_tobtpo.jpg"
             class="w-full h-full object-cover" style="opacity: 0.5;">
    </div>

    <!-- Card login -->
    <div class="relative w-full sm:max-w-md mt-6 px-6 py-4 shadow-md overflow-hidden sm:rounded-lg bg-white">
        {{ $slot }}
    </div>
</div>
