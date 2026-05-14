<!DOCTYPE html>
<html lang="pt_BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400;0,700;1,800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Instrument Sans', sans-serif; }
    </style>
</head>
<body class="bg-transparent antialiased h-full flex items-center justify-center p-4">
    <x-public.author-badge 
        :user="$user" 
        mode="embed" 
        :theme="$theme" 
        :showStats="request()->query('showStats', 'true') === 'true'" 
        :showBio="request()->query('showBio', 'true') === 'true'" 
        :showLocation="request()->query('showLocation', 'true') === 'true'"
        class="w-[480px] shadow-2xl"
    />
</body>
</html>
