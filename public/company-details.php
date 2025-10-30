<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/session.php';

// Only authenticated users can view company details
if (!isAuthenticated()) {
    header("Location: /auth/login.php");
    exit;
}

require_once __DIR__ . '/../controllers/ProfileController.php';
require_once __DIR__ . '/../controllers/JobController.php';
require_once __DIR__ . '/../config/db.php';

$profileController = new ProfileController($conn);
$jobController = new JobController($conn);

// Get company profile data
$data = $profileController->showProfile();
$company = $data['profile'];
$additionalData = $data['additionalData'];

$title = htmlspecialchars($company['company_name'] ?? 'Company Profile') . " - Company Details";

// Get jobs posted by this company
$employerUuid = $company['employer_uuid'] ?? null;
$companyJobs = [];
if ($employerUuid) {
    $companyJobs = $jobController->job->getJobsByEmployer($employerUuid);
}

include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-6xl mx-auto">
    <!-- Company Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col md:flex-row items-start gap-6">
            <?php if (!empty($company['company_logo'])): ?>
            <img src="<?php echo htmlspecialchars($company['company_logo']); ?>" alt="Company Logo"
                class="w-24 h-24 rounded-lg object-cover border-4 border-gray-200">
            <?php endif; ?>

            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">
                    <?php echo htmlspecialchars($company['company_name'] ?? 'Company Name'); ?>
                </h1>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <?php if (!empty($company['industry'])): ?>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span class="text-gray-600"><?php echo htmlspecialchars($company['industry']); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($company['location'])): ?>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        <span class="text-gray-600"><?php echo htmlspecialchars($company['location']); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($company['website'])): ?>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        <a href="<?php echo htmlspecialchars($company['website']); ?>" target="_blank"
                            class="text-blue-600 hover:text-blue-800 hover:underline">
                            <?php echo htmlspecialchars($company['website']); ?>
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($company['contact_number'])): ?>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <a href="tel:<?php echo htmlspecialchars($company['contact_number']); ?>"
                            class="text-blue-600 hover:text-blue-800 hover:underline">
                            <?php echo htmlspecialchars($company['contact_number']); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Company Description -->
    <?php if (!empty($company['about_company'])): ?>
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">About Company</h2>
        <div class="text-gray-700 whitespace-pre-wrap">
            <?php echo nl2br(htmlspecialchars($company['about_company'])); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Jobs Posted by Company -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Jobs Posted by
            <?php echo htmlspecialchars($company['company_name'] ?? 'This Company'); ?></h2>

        <?php if (empty($companyJobs)): ?>
        <div class="text-center py-8">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Jobs Posted Yet</h3>
            <p class="text-gray-500">This company hasn't posted any jobs yet.</p>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($companyJobs as $job): ?>
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-3">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        <a href="/jobs/job-details.php?id=<?php echo htmlspecialchars($job['uuid']); ?>"
                            class="text-blue-600 hover:text-blue-800 hover:underline">
                            <?php echo htmlspecialchars($job['title']); ?>
                        </a>
                    </h3>
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        <?php echo $job['status'] === 'open' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                        <?php echo ucfirst($job['status']); ?>
                    </span>
                </div>

                <div class="space-y-2 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        <span><?php echo htmlspecialchars($job['location']); ?></span>
                    </div>

                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span><?php echo htmlspecialchars(ucfirst($job['job_type'])); ?></span>
                    </div>

                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v10m0 0l-2-2m2 2l2-2" />
                        </svg>
                        <span>Posted <?php echo date('M j, Y', strtotime($job['created_at'])); ?></span>
                    </div>
                </div>

                <?php if (!empty($job['salary_range'])): ?>
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <span class="text-sm font-medium text-green-600">
                        <?php echo htmlspecialchars($job['salary_range']); ?>
                    </span>
                </div>
                <?php endif; ?>

                <div class="mt-4">
                    <a href="/jobs/job-details.php?id=<?php echo htmlspecialchars($job['uuid']); ?>"
                        class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition text-center block text-sm font-medium">
                        View Job Details
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>