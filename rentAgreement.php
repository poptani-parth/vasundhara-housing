<?php
include 'database.php';
session_start();

/**
 * Converts a number into words (Indian numbering system).
 * @param int $num The number to convert.
 * @return string The number in words.
 */
function numberToWords(int $num): string {
    if ($num == 0) return 'Zero';

    $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    $toWords = function($n) use ($ones, $tens) {
        $str = '';
        if ($n >= 100) {
            $str .= $ones[floor($n / 100)] . ' Hundred';
            $n %= 100;
            if ($n > 0) $str .= ' ';
        }
        if ($n >= 20) {
            $str .= $tens[floor($n / 10)];
            $n %= 10;
            if ($n > 0) $str .= ' ';
        }
        if ($n > 0) {
            $str .= $ones[$n];
        }
        return $str;
    };

    $result = '';
    if ($num >= 10000000) { $result .= $toWords(floor($num / 10000000)) . ' Crore '; $num %= 10000000; }
    if ($num >= 100000) { $result .= $toWords(floor($num / 100000)) . ' Lakh '; $num %= 100000; }
    if ($num >= 1000) { $result .= $toWords(floor($num / 1000)) . ' Thousand '; $num %= 1000; }
    if ($num > 0) { $result .= $toWords($num); }

    return trim(preg_replace('/\s+/', ' ', $result));
}

$tenantName = $_GET['name'] ?? null;
$propertyId = filter_var($_GET['property_id'] ?? null, FILTER_SANITIZE_NUMBER_INT);

$tenant = null;
if ($tenantName) {
    // Fetch tenant status as well to check if they have a pending agreement
    $stmt = $conn->prepare("SELECT tenant_id, tenant_name, contact_number, email, status FROM tenants WHERE tenant_name = ?");
    $stmt->bind_param("s", $tenantName);
    $stmt->execute();
    $result = $stmt->get_result();
    $tenant = $result->fetch_assoc();
    $stmt->close();
}

// If we have a tenant and property, check for an existing but unpaid agreement
if ($tenant && $propertyId) {
    $tenant_id = $tenant['tenant_id'];
    $_SESSION['tenant_id'] = $tenant_id;
    $_SESSION['property_id'] = $propertyId;
    $_SESSION['payment_origin'] = 'agreement';

    // Check if an agreement already exists for this tenant and property
    $stmt_check = $conn->prepare("SELECT agreement_id FROM rental_agreements WHERE tenant_id = ? AND pro_id = ?");
    $stmt_check->bind_param("ii", $tenant_id, $propertyId);
    $stmt_check->execute();
    $agreement_result = $stmt_check->get_result();
    $agreement_exists = $agreement_result->num_rows > 0;
    $stmt_check->close();

    // If an agreement exists and the tenant is still 'Unactive', they need to pay
    if ($agreement_exists && $tenant['status'] === 'Unactive') {
        // Redirect to payment gateway to complete the booking
        header('Location: PaymentGateway/index.php');
        exit();
    }
}

$property = null;
if ($propertyId) {
    $stmt = $conn->prepare("SELECT month_rent FROM properties WHERE pro_id = ?");
    $stmt->bind_param("i", $propertyId);
    $stmt->execute();
    $result = $stmt->get_result();
    $property = $result->fetch_assoc();
    $stmt->close();
    // Ensure property_id is in session if not set before
    if (!isset($_SESSION['property_id'])) {
        $_SESSION['property_id'] = $propertyId;
    }
}

$monthRent = $property ? $property['month_rent'] : '0';
$monthRentInWords = numberToWords((int)$monthRent);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Agreement</title>
    <link rel="stylesheet" href="./assets/fonts/Poppins.css">
    <script src="./assets/js/tailwind.js"></script>
    <link href="./assets/css/tailwind.css" rel="stylesheet"/>
    <link href="./assets/css/all.min.css" rel="stylesheet"/>
    <link href="./assets/RemixIcon-master/fonts/remixicon.css" rel="stylesheet"/>
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .form-step { display: none; }
        .form-step.active { display: block; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-4xl w-full bg-white rounded-xl shadow-2xl p-8 md:p-12">
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-gray-800">Rent Agreement Details</h1>
            <p class="mt-4 text-lg text-gray-600">Fill out the form to generate your rent agreement.</p>
        </div>

        <div class="flex justify-between items-center mb-8 px-4">
            <div class="step-indicator flex-1 text-center" data-step="1">
                <div class="indicator-circle w-12 h-12 flex items-center justify-center rounded-full bg-blue-600 text-white font-bold mx-auto">1</div>
                <p class="mt-2 text-sm font-medium text-blue-600">Tenant</p>
            </div>
            <div class="flex-1 h-px bg-gray-300"></div>
            <div class="step-indicator flex-1 text-center" data-step="2">
                <div class="indicator-circle w-12 h-12 flex items-center justify-center rounded-full bg-gray-300 text-gray-600 font-bold mx-auto">2</div>
                <p class="mt-2 text-sm font-medium text-gray-500">Agreement</p>
            </div>
             <div class="flex-1 h-px bg-gray-300"></div>
            <div class="step-indicator flex-1 text-center" data-step="3">
                <div class="indicator-circle w-12 h-12 flex items-center justify-center rounded-full bg-gray-300 text-gray-600 font-bold mx-auto">3</div>
                <p class="mt-2 text-sm font-medium text-gray-500">Witnesses</p>
            </div>
             <div class="flex-1 h-px bg-gray-300"></div>
            <div class="step-indicator flex-1 text-center" data-step="4">
                <div class="indicator-circle w-12 h-12 flex items-center justify-center rounded-full bg-gray-300 text-gray-600 font-bold mx-auto">4</div>
                <p class="mt-2 text-sm font-medium text-gray-500">Documents</p>
            </div>
        </div>

        <form action="store_tenant_details.php" method="POST" id="agreement-form" enctype="multipart/form-data" class="space-y-8">
            <!-- Step 1: Tenant Details -->
            <div id="step1" class="form-step active">
                 <div class="bg-gray-50 p-6 rounded-lg">
                    <h2 class="text-2xl font-bold text-gray-700 mb-6">Tenant Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <input type="text" name="tenant_name" value="<?php echo htmlspecialchars($tenant['tenant_name'] ?? ''); ?>" class="hidden">
                        <div>
                            <label for="tenant_father_name" class="block text-sm font-medium text-gray-700">Father's Name</label>
                            <input type="text" name="tenant_father_name" id="tenant_father_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2" required>
                        </div>
                        <div>
                            <label for="tenant_mobile_number" class="block text-sm font-medium text-gray-700">Mobile Number</label>
                            <input type="text" readonly value="<?php echo htmlspecialchars($tenant['contact_number'] ?? ''); ?>" name="tenant_mobile_number" id="tenant_mobile_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 bg-gray-200" required>
                        </div>
                        <div>
                            <label for="tenant_email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" name="tenant_email" value="<?php echo htmlspecialchars($tenant['email'] ?? ''); ?>" id="tenant_email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2" required>
                        </div>
                        <div>
                            <label for="tenant_aadhar_number" class="block text-sm font-medium text-gray-700">Aadhar Number</label>
                            <input type="text" maxlength="14" name="tenant_aadhar_number" id="tenant_aadhar_number" placeholder="xxxx xxxx xxxx" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2" required>
                        </div>
                        <div class="md:col-span-2">
                            <label for="tenant_address" class="block text-sm font-medium text-gray-700">Current Address</label>
                            <textarea name="tenant_address" id="tenant_address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2" required></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Step 2: Agreement Details -->
            <div id="step2" class="form-step">
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h2 class="text-2xl font-bold text-gray-700 mb-6">Agreement Details</h2>
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="rent_amount_numeric" class="block text-sm font-medium text-gray-700">Monthly Rent (₹)</label>
                            <input readonly type="number" value="<?= $monthRent ?>" name="rent_amount_numeric" id="rent_amount_numeric" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 bg-gray-200" required>
                        </div>
                        <input type="hidden" name="rent_amount_words" value="<?php echo htmlspecialchars($monthRentInWords); ?>">
                        <div>
                            <label for="agreement_date" class="block text-sm font-medium text-gray-700">Agreement Start Date</label>
                            <input type="date" name="agreement_date" id="agreement_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2" required>
                        </div>
                        <div>
                            <label for="agreement_duration" class="block text-sm font-medium text-gray-700">Duration (Months)</label>
                            <input type="number" name="agreement_duration_months" id="agreement_duration" value="11" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2" required>
                        </div>
                         <div>
                            <label for="place" class="block text-sm font-medium text-gray-700">Place of Agreement</label>
                            <input type="text" name="place" id="place" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2" required>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Step 3: Witness Details -->
            <div id="step3" class="form-step">
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h2 class="text-2xl font-bold text-gray-700 mb-6">Witness Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="witness1_name" class="block text-sm font-medium text-gray-700">Witness 1 Name</label>
                            <input type="text" name="witness1_name" id="witness1_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2" required>
                        </div>
                        <div>
                             <label for="witness1_aadhar_card" class="block text-sm font-medium text-gray-700">Witness 1 Aadhar Card</label>
                            <input type="file" name="witness1_aadhar_card" id="witness1_aadhar_card" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0" required accept="image/*">
                        </div>
                         <div>
                            <label for="witness2_name" class="block text-sm font-medium text-gray-700">Witness 2 Name</label>
                            <input type="text" name="witness2_name" id="witness2_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2" required>
                        </div>
                        <div>
                            <label for="witness2_aadhar_card" class="block text-sm font-medium text-gray-700">Witness 2 Aadhar Card</label>
                            <input type="file" name="witness2_aadhar_card" id="witness2_aadhar_card" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0" required accept="image/*">
                        </div>
                    </div>
                </div>
            </div>
            <!-- Step 4: Document Uploads -->
            <div id="step4" class="form-step">
                 <div class="bg-gray-50 p-6 rounded-lg">
                    <h2 class="text-2xl font-bold text-gray-700 mb-6">Document Uploads</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="tenant_photo" class="block text-sm font-medium text-gray-700">Tenant Photo</label>
                            <input type="file" name="tenant_photo" id="tenant_photo" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0" required accept="image/*">
                        </div>
                         <div>
                            <label for="aadhar_card" class="block text-sm font-medium text-gray-700">Tenant Aadhar Card</label>
                            <input type="file" name="aadhar_card" id="aadhar_card" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0" required accept="image/*">
                        </div>
                         <div>
                            <label for="tenant_signature" class="block text-sm font-medium text-gray-700">Tenant Signature</label>
                            <input type="file" name="tenant_signature" id="tenant_signature" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0" required accept="image/*">
                        </div>
                         <div>
                            <label for="witness1_signature" class="block text-sm font-medium text-gray-700">Witness 1 Signature</label>
                            <input type="file" name="witness1_signature" id="witness1_signature" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0" required accept="image/*">
                        </div>
                         <div>
                            <label for="witness2_signature" class="block text-sm font-medium text-gray-700">Witness 2 Signature</label>
                            <input type="file" name="witness2_signature" id="witness2_signature" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0" required accept="image/*">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between mt-10">
                <button type="button" id="prev-btn" class="px-8 py-3 bg-gray-300 text-gray-700 font-bold rounded-lg" style="display:none;">Previous</button>
                <button type="button" id="next-btn" class="px-8 py-3 bg-blue-600 text-white font-bold rounded-lg ml-auto">Next</button>
                <button type="submit" name="tenantDetails" id="submit-btn" class="px-8 py-3 bg-green-600 text-white font-bold rounded-lg" style="display:none;">Submit</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const steps = document.querySelectorAll('.form-step');
            const indicators = document.querySelectorAll('.step-indicator');
            const nextBtn = document.getElementById('next-btn');
            const prevBtn = document.getElementById('prev-btn');
            const submitBtn = document.getElementById('submit-btn');
            let currentStep = 1;

            function updateUI() {
                steps.forEach(step => step.classList.remove('active'));
                document.getElementById(`step${currentStep}`).classList.add('active');

                indicators.forEach(indicator => {
                    const step = parseInt(indicator.getAttribute('data-step'));
                    const circle = indicator.querySelector('.indicator-circle');
                    const text = indicator.querySelector('p');

                    if (step < currentStep) {
                        circle.classList.add('bg-green-500', 'text-white');
                        circle.classList.remove('bg-blue-600', 'bg-gray-300');
                        text.classList.add('text-green-600');
                        text.classList.remove('text-blue-600', 'text-gray-500');
                    } else if (step === currentStep) {
                        circle.classList.add('bg-blue-600', 'text-white');
                        circle.classList.remove('bg-green-500', 'bg-gray-300');
                         text.classList.add('text-blue-600');
                        text.classList.remove('text-green-600', 'text-gray-500');
                    } else {
                        circle.classList.add('bg-gray-300', 'text-gray-600');
                        circle.classList.remove('bg-blue-600', 'bg-green-500');
                        text.classList.add('text-gray-500');
                        text.classList.remove('text-blue-600', 'text-green-600');
                    }
                });

                prevBtn.style.display = currentStep > 1 ? 'inline-block' : 'none';
                nextBtn.style.display = currentStep < steps.length ? 'inline-block' : 'none';
                submitBtn.style.display = currentStep === steps.length ? 'inline-block' : 'none';
            }
            
            function validateStep() {
                const currentFormSection = document.getElementById(`step${currentStep}`);
                const requiredInputs = currentFormSection.querySelectorAll('[required]');
                for (const input of requiredInputs) {
                    if (!input.value.trim()) {
                        alert('Please fill all required fields.');
                        return false;
                    }
                }
                return true;
            }

            nextBtn.addEventListener('click', () => {
                if (validateStep() && currentStep < steps.length) {
                    currentStep++;
                    updateUI();
                }
            });

            prevBtn.addEventListener('click', () => {
                if (currentStep > 1) {
                    currentStep--;
                    updateUI();
                }
            });

            updateUI();
        });
    </script>
</body>
</html>
