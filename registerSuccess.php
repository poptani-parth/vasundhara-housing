<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success</title>
    <link rel="stylesheet" href="./assets/fonts/Poppins.css">
    <script src="./assets/js/tailwind.js"></script>
    <link href="./assets/css/tailwind.css" rel="stylesheet"/>
    <link href="./assets/css/all.min.css" rel="stylesheet"/>
    <link href="./assets/RemixIcon-master/fonts/remixicon.css" rel="stylesheet"/>
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .animate-in-out {
            transition: transform 0.3s ease-out, opacity 0.3s ease-out;
        }
        .modal-hidden {
            transform: scale(0.95);
            opacity: 0;
        }
        .modal-shown {
            transform: scale(1);
            opacity: 1;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div id="success-modal" class="fixed inset-0 z-50 bg-gray-900 bg-opacity-75 flex justify-center items-center p-4">
        <div id="modal-content" class="bg-white rounded-xl shadow-2xl p-6 sm:p-8 w-full max-w-sm text-center animate-in-out modal-hidden">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900">Success!</h3>
            <div class="mt-2">
                <p class="text-sm text-gray-500">
                    Your details have been submitted. You will now be redirected to the payment page.
                </p>
            </div>
            <div class="mt-5 sm:mt-6">
                <a href="PaymentGateway/index.php" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-6 py-3 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700">
                    Proceed to Payment
                </a>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('success-modal');
        const modalContent = document.getElementById('modal-content');

        function showModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                modalContent.classList.remove('modal-hidden');
                modalContent.classList.add('modal-shown');
                 setTimeout(() => {
                    window.location.href = "PaymentGateway/index.php";
                }, 3000); // Redirect after 3 seconds
            }, 10);
        }

        window.onload = showModal;
    </script>

</body>
</html>
