<?php
$title = "Job Portal - Find Your Dream Job";
include __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<div class="bg-blue-600 text-white py-20">
    <div class="container mx-auto text-center">
        <h1 class="text-5xl font-bold mb-4">Find Your Dream Job Today</h1>
        <p class="text-xl mb-8">Connect with top employers and discover opportunities that match your skills</p>
    </div>
</div>

<!-- Search and Filter Section -->
<div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md mt-8">
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Search for Jobs</h2>
        <p class="text-gray-600">Use filters to find the perfect job for you</p>
    </div>
    <form method="GET" action="home.php" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Jobs</label>
            <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                placeholder="Job title, company, or keywords"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label for="industry" class="block text-sm font-medium text-gray-700 mb-1">Industry</label>
            <select name="industry" id="industry"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Industries</option>
                <option value="Technology" <?php echo ($_GET['industry'] ?? '') === 'Technology' ? 'selected' : ''; ?>>
                    Technology</option>
                <option value="Marketing" <?php echo ($_GET['industry'] ?? '') === 'Marketing' ? 'selected' : ''; ?>>
                    Marketing</option>
                <option value="Finance" <?php echo ($_GET['industry'] ?? '') === 'Finance' ? 'selected' : ''; ?>>Finance
                </option>
                <option value="Healthcare" <?php echo ($_GET['industry'] ?? '') === 'Healthcare' ? 'selected' : ''; ?>>
                    Healthcare</option>
                <option value="Education" <?php echo ($_GET['industry'] ?? '') === 'Education' ? 'selected' : ''; ?>>
                    Education</option>
                <option value="Retail" <?php echo ($_GET['industry'] ?? '') === 'Retail' ? 'selected' : ''; ?>>Retail
                </option>
                <option value="Manufacturing"
                    <?php echo ($_GET['industry'] ?? '') === 'Manufacturing' ? 'selected' : ''; ?>>Manufacturing
                </option>
                <option value="Consulting" <?php echo ($_GET['industry'] ?? '') === 'Consulting' ? 'selected' : ''; ?>>
                    Consulting</option>
            </select>
        </div>
        <div>
            <label for="job_type" class="block text-sm font-medium text-gray-700 mb-1">Job Type</label>
            <select name="job_type" id="job_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Types</option>
                <option value="full-time" <?php echo ($_GET['job_type'] ?? '') === 'full-time' ? 'selected' : ''; ?>>
                    Full Time</option>
                <option value="part-time" <?php echo ($_GET['job_type'] ?? '') === 'part-time' ? 'selected' : ''; ?>>
                    Part Time</option>
                <option value="contract" <?php echo ($_GET['job_type'] ?? '') === 'contract' ? 'selected' : ''; ?>>
                    Contract</option>
                <option value="freelance" <?php echo ($_GET['job_type'] ?? '') === 'freelance' ? 'selected' : ''; ?>>
                    Freelance</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit"
                class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Search Jobs
            </button>
        </div>
    </form>
</div>

<!-- Employer and Jobseeker Cards -->
<div class="max-w-4xl mx-auto mt-12 grid grid-cols-1 md:grid-cols-2 gap-8">
    <!-- Employer Card -->
    <div class="bg-white p-8 rounded-lg shadow-md text-center">
        <div class="mb-4">
            <svg class="mx-auto h-12 w-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                </path>
            </svg>
        </div>
        <h2 class="text-2xl font-bold mb-4">For Employers</h2>
        <p class="text-gray-600 mb-6">Post jobs and find the perfect candidates for your team.</p>
        <a href="/auth/register.php?role=employer"
            class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700">Get Started</a>
    </div>

    <!-- Jobseeker Card -->
    <div class="bg-white p-8 rounded-lg shadow-md text-center">
        <div class="mb-4">
            <svg class="mx-auto h-12 w-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
        </div>
        <h2 class="text-2xl font-bold mb-4">For Job Seekers</h2>
        <p class="text-gray-600 mb-6">Browse thousands of jobs and apply to your favorites.</p>
        <a href="/auth/register.php?role=jobseeker"
            class="bg-green-600 text-white px-6 py-3 rounded-md hover:bg-green-700">Get Started</a>
    </div>
</div>

<!-- Explore Jobs Button -->
<div class="text-center mt-12">
    <a href="home.php" class="bg-green-600 text-white px-8 py-4 rounded-md text-lg hover:bg-green-700">Explore Jobs</a>
</div>

<!-- Explore Jobs by Function Section -->
<div class="max-w-6xl mx-auto mt-16">
    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Explore Jobs in Kenya by Job Function</h2>
        <p class="text-gray-600">Find opportunities in your preferred field</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="home.php?search=Accounting%2C+Auditing+%26+Finance"
            class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center hover:bg-gray-50">
            <h3 class="font-semibold text-gray-900">Accounting, Auditing & Finance</h3>
        </a>
        <a href="home.php?search=Admin+%26+Office"
            class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center hover:bg-gray-50">
            <h3 class="font-semibold text-gray-900">Admin & Office</h3>
        </a>
        <a href="home.php?search=Creative+%26+Design"
            class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center hover:bg-gray-50">
            <h3 class="font-semibold text-gray-900">Creative & Design</h3>
        </a>
        <a href="home.php?search=Building+%26+Architecture"
            class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center hover:bg-gray-50">
            <h3 class="font-semibold text-gray-900">Building & Architecture</h3>
        </a>
        <a href="home.php?search=Consulting+%26+Strategy"
            class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center hover:bg-gray-50">
            <h3 class="font-semibold text-gray-900">Consulting & Strategy</h3>
        </a>
        <a href="home.php?search=Customer+Service+%26+Support"
            class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center hover:bg-gray-50">
            <h3 class="font-semibold text-gray-900">Customer Service & Support</h3>
        </a>
        <a href="home.php?search=Engineering+%26+Technology"
            class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center hover:bg-gray-50">
            <h3 class="font-semibold text-gray-900">Engineering & Technology</h3>
        </a>
        <a href="home.php?search=Farming+%26+Agriculture"
            class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center hover:bg-gray-50">
            <h3 class="font-semibold text-gray-900">Farming & Agriculture</h3>
        </a>
        <a href="home.php?search=Food+Services+%26+Catering"
            class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center hover:bg-gray-50">
            <h3 class="font-semibold text-gray-900">Food Services & Catering</h3>
        </a>
        <a href="home.php?search=Hospitality+%26+Leisure"
            class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center hover:bg-gray-50">
            <h3 class="font-semibold text-gray-900">Hospitality & Leisure</h3>
        </a>
        <a href="home.php?search=Software+%26+Data"
            class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center hover:bg-gray-50">
            <h3 class="font-semibold text-gray-900">Software & Data</h3>
        </a>
        <a href="home.php?search=Legal+Services"
            class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center hover:bg-gray-50">
            <h3 class="font-semibold text-gray-900">Legal Services</h3>
        </a>
        <a href="home.php?search=Marketing+%26+Communications"
            class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center hover:bg-gray-50">
            <h3 class="font-semibold text-gray-900">Marketing & Communications</h3>
        </a>
        <a href="home.php?search=Medical+%26+Pharmaceutical"
            class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center hover:bg-gray-50">
            <h3 class="font-semibold text-gray-900">Medical & Pharmaceutical</h3>
        </a>
        <a href="home.php?search=Product+%26+Project+Management"
            class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center hover:bg-gray-50">
            <h3 class="font-semibold text-gray-900">Product & Project Management</h3>
        </a>


        <a href="home.php?search=Human+Resources"
            class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center hover:bg-gray-50">
            <h3 class="font-semibold text-gray-900">Human Resources</h3>
        </a>
        <a href="home.php?search=Management+%26+Business+Development"
            class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center hover:bg-gray-50">
            <h3 class="font-semibold text-gray-900">Management & Business Development</h3>
        </a>
        <a href="home.php?search=Community+%26+Social+Services"
            class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center hover:bg-gray-50">
            <h3 class="font-semibold text-gray-900">Community & Social Services</h3>
        </a>
        <a href="home.php?search=Sales"
            class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center hover:bg-gray-50">
            <h3 class="font-semibold text-gray-900">Sales</h3>
        </a>
        <a href="home.php?search=Supply+Chain+%26+Procurement"
            class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center hover:bg-gray-50">
            <h3 class="font-semibold text-gray-900">Supply Chain & Procurement</h3>
        </a>

        <a href="home.php?search=Health+%26+Safety"
            class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center hover:bg-gray-50">
            <h3 class="font-semibold text-gray-900">Health & Safety</h3>
        </a>
    </div>
</div>



<?php include __DIR__ . '/../includes/footer.php'; ?>