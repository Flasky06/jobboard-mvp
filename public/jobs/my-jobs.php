<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/JobController.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../helpers/csrf.php';

$jobController = new JobController($conn);
$jobs = $jobController->viewJobs();

$title = "My Posted Jobs";
include __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-6xl mx-auto bg-white p-4 md:p-8 rounded-lg shadow-md mt-4 md:mt-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">My Posted Jobs</h2>
        <a href="post-job.php"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            Post New Job
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?php echo htmlspecialchars($_SESSION['success']); ?>
    </div>
    <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['errors'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul class="list-disc list-inside">
            <?php foreach ($_SESSION['errors'] as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <?php if (empty($jobs)): ?>
    <div class="text-center py-12">
        <div class="text-gray-500 mb-4">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m8 0V8a2 2 0 01-2 2H8a2 2 0 01-2-2V6m8 0H8m0 0V4" />
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No jobs posted yet</h3>
        <p class="text-gray-500 mb-4">Get started by posting your first job opening.</p>
        <a href="post-job.php"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            Post Your First Job
        </a>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($jobs as $job): ?>
        <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start">
                <div class="flex-1 cursor-pointer"
                    onclick="window.location.href='job-details.php?id=<?php echo $job['uuid']; ?>'">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2 hover:text-blue-600">
                        <?php echo htmlspecialchars($job['title']); ?>
                    </h3>
                    <div class="flex items-center space-x-4 text-sm text-gray-600 mb-3">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <?php echo htmlspecialchars($job['location']); ?>
                        </span>
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <?php echo htmlspecialchars(ucfirst($job['job_type'])); ?>
                        </span>
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v10m0 0l-2-2m2 2l2-2" />
                            </svg>
                            Posted <?php echo date('M j, Y', strtotime($job['created_at'])); ?>
                        </span>
                    </div>
                    <p class="text-gray-700 mb-3 line-clamp-2">
                        <?php echo htmlspecialchars(substr($job['job_description'], 0, 200)) . (strlen($job['job_description']) > 200 ? '...' : ''); ?>
                    </p>
                    <?php if (!empty($job['salary_range'])): ?>
                    <p class="text-green-600 font-medium mb-3">
                        <?php echo htmlspecialchars($job['salary_range']); ?>
                    </p>
                    <?php endif; ?>
                </div>
                <div class="flex flex-col sm:flex-row gap-2 sm:space-x-2 ml-0 sm:ml-4 mt-4 sm:mt-0">

                    <a href="job-applications.php?id=<?php echo $job['uuid']; ?>"
                        class="bg-green-500 text-white px-3 py-2 rounded text-sm hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 mobile-btn-full">
                        View Applications
                    </a>


                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>