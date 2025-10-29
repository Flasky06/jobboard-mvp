<?php
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../controllers/JobController.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/csrf.php';

$jobController = new JobController($conn);

$jobId = $_GET['id'] ?? 0;
$job = $jobController->job->getJobById($jobId);

if (!$job) {
    header("Location: ../index.php");
    exit;
}

// Check if user is the employer who posted this job
$canEdit = false;
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'employer') {
    $employer = $jobController->user->getUserProfile($_SESSION['user_id']);
    if ($employer && isset($employer['employer_uuid']) && $employer['employer_uuid'] === $job['employer_uuid']) {
        $canEdit = true;
    }
}

if (!$canEdit) {
    header("Location: ../index.php");
    exit;
}

// ===== HANDLE FORM SUBMISSION =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['errors'] = ['Invalid security token. Please try again.'];
        header("Location: edit-job.php?id=" . $jobId);
        exit;
    }

    // Collect form data
    $jobData = [
        'title' => trim($_POST['title'] ?? ''),
        'job_level' => trim($_POST['job_level'] ?? ''),
        'job_type' => trim($_POST['job_type'] ?? ''),
        'industry' => trim($_POST['industry'] ?? ''),
        'location' => trim($_POST['location'] ?? ''),
        'salary_range' => trim($_POST['salary_range'] ?? ''),
        'application_deadline' => !empty($_POST['application_deadline']) ? $_POST['application_deadline'] : null,
        'job_description' => trim($_POST['job_description'] ?? ''),
        'requirements_qualifications' => trim($_POST['requirements_qualifications'] ?? ''),
        'additional_information' => trim($_POST['additional_information'] ?? '')
    ];

    // Validate required fields
    $errors = [];
    if (empty($jobData['title'])) {
        $errors[] = 'Job title is required';
    }
    if (empty($jobData['job_type'])) {
        $errors[] = 'Job type is required';
    }
    if (empty($jobData['location'])) {
        $errors[] = 'Location is required';
    }
    if (empty($jobData['job_description'])) {
        $errors[] = 'Job description is required';
    }
    if (empty($jobData['requirements_qualifications'])) {
        $errors[] = 'Requirements and qualifications are required';
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: edit-job.php?id=" . $jobId);
        exit;
    }

    // Update the job using the correct method signature (3 parameters)
    if ($jobController->job->updateJob($jobId, $employer['employer_uuid'], $jobData)) {
        $_SESSION['success'] = 'Job updated successfully!';
        header("Location: view-jobs.php");
        exit;
    } else {
        $_SESSION['errors'] = ['Failed to update job. Please try again.'];
        header("Location: edit-job.php?id=" . $jobId);
        exit;
    }
}
// ===== END OF POST HANDLING =====

$title = "Edit Job - " . htmlspecialchars($job['title']);
include __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Edit Job</h1>
        <p class="text-gray-600 mb-6">Update your job posting details.</p>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['errors'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php unset($_SESSION['errors']); ?>
        </div>
        <?php endif; ?>

        <form action="edit-job.php?id=<?php echo $job['uuid']; ?>" method="POST">
            <?php echo csrf_field(); ?>

            <div class="grid md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-gray-700 mb-2">Job Title *</label>
                    <input type="text" name="title" required value="<?php echo htmlspecialchars($job['title']); ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-gray-700 mb-2">Job Level</label>
                    <select name="job_level"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Level</option>
                        <option value="Entry Level"
                            <?php echo ($job['job_level'] ?? '') === 'Entry Level' ? 'selected' : ''; ?>>Entry Level
                        </option>
                        <option value="Mid Level"
                            <?php echo ($job['job_level'] ?? '') === 'Mid Level' ? 'selected' : ''; ?>>Mid Level
                        </option>
                        <option value="Senior Level"
                            <?php echo ($job['job_level'] ?? '') === 'Senior Level' ? 'selected' : ''; ?>>Senior Level
                        </option>
                        <option value="Executive"
                            <?php echo ($job['job_level'] ?? '') === 'Executive' ? 'selected' : ''; ?>>Executive
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 mb-2">Job Type *</label>
                    <select name="job_type" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Type</option>
                        <option value="full-time"
                            <?php echo ($job['job_type'] ?? '') === 'full-time' ? 'selected' : ''; ?>>Full Time</option>
                        <option value="part-time"
                            <?php echo ($job['job_type'] ?? '') === 'part-time' ? 'selected' : ''; ?>>Part Time</option>
                        <option value="contract"
                            <?php echo ($job['job_type'] ?? '') === 'contract' ? 'selected' : ''; ?>>Contract</option>
                        <option value="freelance"
                            <?php echo ($job['job_type'] ?? '') === 'freelance' ? 'selected' : ''; ?>>Freelance</option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 mb-2">Industry</label>
                    <select name="industry"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Industry</option>
                        <option value="Technology"
                            <?php echo ($job['industry'] ?? '') === 'Technology' ? 'selected' : ''; ?>>Technology
                        </option>
                        <option value="Marketing"
                            <?php echo ($job['industry'] ?? '') === 'Marketing' ? 'selected' : ''; ?>>Marketing</option>
                        <option value="Finance" <?php echo ($job['industry'] ?? '') === 'Finance' ? 'selected' : ''; ?>>
                            Finance</option>
                        <option value="Healthcare"
                            <?php echo ($job['industry'] ?? '') === 'Healthcare' ? 'selected' : ''; ?>>Healthcare
                        </option>
                        <option value="Education"
                            <?php echo ($job['industry'] ?? '') === 'Education' ? 'selected' : ''; ?>>Education</option>
                        <option value="Retail" <?php echo ($job['industry'] ?? '') === 'Retail' ? 'selected' : ''; ?>>
                            Retail</option>
                        <option value="Manufacturing"
                            <?php echo ($job['industry'] ?? '') === 'Manufacturing' ? 'selected' : ''; ?>>Manufacturing
                        </option>
                        <option value="Consulting"
                            <?php echo ($job['industry'] ?? '') === 'Consulting' ? 'selected' : ''; ?>>Consulting
                        </option>
                        <option value="Other" <?php echo ($job['industry'] ?? '') === 'Other' ? 'selected' : ''; ?>>
                            Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 mb-2">Location *</label>
                    <input type="text" name="location" required
                        value="<?php echo htmlspecialchars($job['location']); ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-gray-700 mb-2">Salary Range</label>
                    <input type="text" name="salary_range"
                        value="<?php echo htmlspecialchars($job['salary_range'] ?? ''); ?>"
                        placeholder="e.g., KES 50,000 - 80,000"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-gray-700 mb-2">Application Deadline</label>
                    <input type="date" name="application_deadline"
                        value="<?php echo htmlspecialchars($job['application_deadline'] ?? ''); ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-gray-700 mb-2">Job Description *</label>
                    <textarea name="job_description" rows="6" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($job['job_description']); ?></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-gray-700 mb-2">Requirements & Qualifications *</label>
                    <textarea name="requirements_qualifications" rows="6" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($job['requirements_qualifications']); ?></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-gray-700 mb-2">Additional Information</label>
                    <textarea name="additional_information" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($job['additional_information'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-6">
                <a href="view-jobs.php"
                    class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 transition">Cancel</a>
                <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Update Job</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>