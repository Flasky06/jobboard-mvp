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

<!-- Main Container with 30/60 Split Layout -->
<div class="flex">
    <!-- Left Sidebar - Company Profile Card (30%) -->
    <?php include __DIR__ . '/../../includes/employer_profile_card.php'; ?>

    <!-- Right Content Area (60%) -->
    <div class="main-content-area lg:ml-[30%] lg:w-[60%] w-full mx-auto">
        <div class="bg-white rounded-lg shadow-md mt-8 p-8">
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">My Posted Jobs</h1>
                    <p class="text-gray-600">Manage and track all your job postings</p>
                </div>
                <a href="/jobs/post-job.php"
                    class="w-full md:w-auto px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-center">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Post New Job
                </a>
            </div>

            <!-- Success Message -->
            <?php if (isset($_SESSION['success'])): ?>
            <div
                class="bg-green-50 border-l-4 border-green-500 text-green-800 px-6 py-4 rounded-lg mb-6 flex items-start">
                <i class="fas fa-check-circle text-green-500 mr-3 mt-0.5"></i>
                <div class="flex-1">
                    <?php echo htmlspecialchars($_SESSION['success']); ?>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <!-- Error Messages -->
            <?php if (isset($_SESSION['errors'])): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-6 py-4 rounded-lg mb-6">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-3 mt-0.5"></i>
                    <ul class="list-disc list-inside space-y-1">
                        <?php foreach ($_SESSION['errors'] as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>

            <!-- Jobs List -->
            <?php if (empty($jobs)): ?>
            <!-- Empty State -->
            <div class="text-center py-16 bg-gray-50 rounded-lg">
                <div class="mb-6">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-4">
                        <i class="fas fa-briefcase text-blue-600 text-3xl"></i>
                    </div>
                </div>
                <h3 class="text-2xl font-semibold text-gray-900 mb-2">No Jobs Posted Yet</h3>
                <p class="text-gray-600 mb-6 max-w-md mx-auto">
                    Start attracting top talent by posting your first job opening. It only takes a few minutes!
                </p>
                <a href="/jobs/post-job.php"
                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Post Your First Job
                </a>
            </div>
            <?php else: ?>
            <!-- Jobs Grid -->
            <div class="space-y-5">
                <?php foreach ($jobs as $job): ?>
                <div
                    class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow bg-white">
                    <!-- Job Card Header -->
                    <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-6 border-b border-gray-200">
                        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4">
                            <div class="flex-1">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <h3 class="text-xl font-bold text-gray-900 mb-2 hover:text-blue-600 cursor-pointer transition-colors"
                                            onclick="window.location.href='/jobs/job-details.php?uuid=<?php echo htmlspecialchars($job['uuid']); ?>'">
                                            <?php echo htmlspecialchars($job['title']); ?>
                                        </h3>

                                        <!-- Job Meta Info -->
                                        <div class="flex flex-wrap gap-3 text-sm text-gray-600 mb-3">
                                            <span class="flex items-center">
                                                <i class="fas fa-map-marker-alt mr-1.5 text-gray-400"></i>
                                                <?php echo htmlspecialchars($job['location']); ?>
                                            </span>
                                            <span class="flex items-center">
                                                <i class="fas fa-briefcase mr-1.5 text-gray-400"></i>
                                                <?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $job['job_type']))); ?>
                                            </span>
                                            <span class="flex items-center">
                                                <i class="fas fa-calendar mr-1.5 text-gray-400"></i>
                                                Posted <?php echo date('M j, Y', strtotime($job['created_at'])); ?>
                                            </span>
                                        </div>

                                        <!-- Job Tags -->
                                        <div class="flex flex-wrap gap-2">
                                            <?php if (!empty($job['industry'])): ?>
                                            <span
                                                class="px-3 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded-full">
                                                <?php echo htmlspecialchars($job['industry']); ?>
                                            </span>
                                            <?php endif; ?>

                                            <?php if (!empty($job['salary_range'])): ?>
                                            <span
                                                class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                                <i class="fas fa-money-bill-wave mr-1"></i>
                                                <?php echo htmlspecialchars($job['salary_range']); ?>
                                            </span>
                                            <?php endif; ?>

                                            <?php if (!empty($job['status'])): ?>
                                            <span
                                                class="px-3 py-1 <?php echo $job['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?> text-xs font-medium rounded-full">
                                                <i class="fas fa-circle mr-1" style="font-size: 6px;"></i>
                                                <?php echo ucfirst($job['status']); ?>
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Job Card Body -->
                    <div class="p-6">
                        <!-- Job Description Preview -->
                        <p class="text-gray-700 mb-4 line-clamp-3 leading-relaxed">
                            <?php echo htmlspecialchars($job['job_description']); ?>
                        </p>

                        <!-- Job Stats -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">
                                    <?php echo $job['applications_count'] ?? 0; ?>
                                </div>
                                <div class="text-xs text-gray-600">Applications</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">
                                    <?php echo $job['views_count'] ?? 0; ?>
                                </div>
                                <div class="text-xs text-gray-600">Views</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600">
                                    <?php 
                                    if (!empty($job['deadline'])) {
                                        $days = floor((strtotime($job['deadline']) - time()) / 86400);
                                        echo max(0, $days);
                                    } else {
                                        echo '--';
                                    }
                                    ?>
                                </div>
                                <div class="text-xs text-gray-600">Days Left</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-orange-600">
                                    <?php echo $job['shortlisted_count'] ?? 0; ?>
                                </div>
                                <div class="text-xs text-gray-600">Shortlisted</div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap gap-3">
                            <a href="/jobs/job-details.php?uuid=<?php echo htmlspecialchars($job['uuid']); ?>"
                                class="flex-1 min-w-[140px] px-4 py-2.5 bg-blue-600 text-white text-center rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm">
                                <i class="fas fa-eye mr-2"></i>
                                View Details
                            </a>

                            <a href="/applications/job-applications.php?id=<?php echo htmlspecialchars($job['uuid']); ?>"
                                class="flex-1 min-w-[140px] px-4 py-2.5 bg-green-600 text-white text-center rounded-lg hover:bg-green-700 transition-colors font-medium text-sm">
                                <i class="fas fa-users mr-2"></i>
                                Applications (<?php echo $job['applications_count'] ?? 0; ?>)
                            </a>

                            <a href="/jobs/edit-job.php?uuid=<?php echo htmlspecialchars($job['uuid']); ?>"
                                class="px-4 py-2.5 bg-gray-100 text-gray-700 text-center rounded-lg hover:bg-gray-200 transition-colors font-medium text-sm">
                                <i class="fas fa-edit mr-2"></i>
                                Edit
                            </a>

                            <button onclick="confirmDelete('<?php echo htmlspecialchars($job['uuid']); ?>')"
                                class="px-4 py-2.5 bg-red-100 text-red-700 text-center rounded-lg hover:bg-red-200 transition-colors font-medium text-sm">
                                <i class="fas fa-trash mr-2"></i>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Responsive adjustments */
@media (max-width: 1024px) {
    .main-content-area {
        margin-left: 0 !important;
        width: 100% !important;
    }
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Smooth transitions for job cards */
.bg-white:hover {
    transform: translateY(-2px);
    transition: all 0.3s ease;
}
</style>

<script>
function confirmDelete(jobUuid) {
    if (confirm('Are you sure you want to delete this job posting? This action cannot be undone.')) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/jobs/delete-job.php';

        const uuidInput = document.createElement('input');
        uuidInput.type = 'hidden';
        uuidInput.name = 'uuid';
        uuidInput.value = jobUuid;

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?php echo generate_csrf_token(); ?>';

        form.appendChild(uuidInput);
        form.appendChild(csrfInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-dismiss success messages after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const successMessage = document.querySelector('.bg-green-50');
    if (successMessage) {
        setTimeout(() => {
            successMessage.style.opacity = '0';
            successMessage.style.transition = 'opacity 0.5s ease';
            setTimeout(() => successMessage.remove(), 500);
        }, 5000);
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>