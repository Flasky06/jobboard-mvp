<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/JobController.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../helpers/csrf.php';

$jobController = new JobController($conn);
$jobController->postJob();

$title = "Post a Job";
include __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md mt-8">
    <h2 class="text-2xl font-bold mb-6 text-center">Post a New Job</h2>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <p><?php echo htmlspecialchars($_SESSION['success']); ?></p>
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

    <form action="post-job.php" method="post" class="space-y-6">
        <?php echo csrf_field(); ?>

        <div>
            <label for="title" class="block text-sm font-medium text-gray-700">Job Title *</label>
            <input type="text" id="title" name="title" required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="e.g., Senior PHP Developer">
        </div>

        <div>
            <label for="job_description" class="block text-sm font-medium text-gray-700">Job Description *</label>
            <textarea id="job_description" name="job_description" rows="6" required
                class="tinymce-advanced mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="Describe the job responsibilities, requirements, and what the candidate will be doing..."></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="location" class="block text-sm font-medium text-gray-700">Location *</label>
                <input type="text" id="location" name="location" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="e.g., Nairobi, Kenya or Remote">
            </div>

            <div>
                <label for="job_type" class="block text-sm font-medium text-gray-700">Job Type</label>
                <select id="job_type" name="job_type"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select job type</option>
                    <option value="Full-time">Full-time</option>
                    <option value="Part-time">Part-time</option>
                    <option value="Contract">Contract</option>
                    <option value="Freelance">Freelance</option>
                    <option value="Internship">Internship</option>
                    <option value="Temporary">Temporary</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="job_level" class="block text-sm font-medium text-gray-700">Job Level</label>
                <select id="job_level" name="job_level"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select job level</option>
                    <option value="Entry Level">Entry Level</option>
                    <option value="Mid Level">Mid Level</option>
                    <option value="Senior Level">Senior Level</option>
                    <option value="Executive">Executive</option>
                </select>
            </div>

            <div>
                <label for="industry" class="block text-sm font-medium text-gray-700">Industry</label>
                <select id="industry" name="industry"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select industry</option>
                    <option value="Technology">Technology</option>
                    <option value="Healthcare">Healthcare</option>
                    <option value="Finance">Finance</option>
                    <option value="Education">Education</option>
                    <option value="Manufacturing">Manufacturing</option>
                    <option value="Retail">Retail</option>
                    <option value="Construction">Construction</option>
                    <option value="Transportation">Transportation</option>
                    <option value="Hospitality">Hospitality</option>
                    <option value="Other">Other</option>
                </select>
            </div>
        </div>

        <div>
            <label for="salary_range" class="block text-sm font-medium text-gray-700">Salary Range</label>
            <input type="text" id="salary_range" name="salary_range"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="e.g., KSh 50,000 - 80,000 or Negotiable">
        </div>

        <div>
            <label for="requirements_qualifications" class="block text-sm font-medium text-gray-700">Requirements &
                Qualifications *</label>
            <textarea id="requirements_qualifications" name="requirements_qualifications" rows="4" required
                class="tinymce-basic mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="List the required skills, experience, qualifications, etc."></textarea>
        </div>

        <div>
            <label for="additional_information" class="block text-sm font-medium text-gray-700">Additional
                Information</label>
            <textarea id="additional_information" name="additional_information" rows="3"
                class="tinymce-basic mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="Any additional information about the job, company culture, benefits, etc."></textarea>
        </div>

        <div>
            <label for="application_deadline" class="block text-sm font-medium text-gray-700">Application
                Deadline</label>
            <input type="date" id="application_deadline" name="application_deadline"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="flex gap-4">
            <button type="submit"
                class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Post Job
            </button>
            <a href="../home"
                class="flex-1 bg-gray-500 text-white py-2 px-4 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 text-center">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const jobDescription = document.getElementById('job_description').value.trim();
        const location = document.getElementById('location').value.trim();
        const requirements = document.getElementById('requirements_qualifications').value.trim();

        if (title.length < 3) {
            e.preventDefault();
            alert('Job title must be at least 3 characters long.');
            return false;
        }

        if (jobDescription.length < 10) {
            e.preventDefault();
            alert('Job description must be at least 10 characters long.');
            return false;
        }

        if (location.length < 2) {
            e.preventDefault();
            alert('Please provide a valid location.');
            return false;
        }

        if (requirements.length < 10) {
            e.preventDefault();
            alert('Requirements must be at least 10 characters long.');
            return false;
        }
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>