/**
 * TinyMCE Configuration for Job Portal
 * Provides rich text editing for job descriptions, cover letters, and other text areas
 */

// Basic configuration for simple text areas (cover letters)
function initBasicEditor(selector) {
  tinymce.init({
    selector: selector,
    height: 300,
    menubar: false,
    plugins: [
      "advlist",
      "autolink",
      "lists",
      "link",
      "image",
      "charmap",
      "preview",
      "anchor",
      "searchreplace",
      "visualblocks",
      "code",
      "fullscreen",
      "insertdatetime",
      "media",
      "table",
      "help",
      "wordcount",
    ],
    toolbar:
      "undo redo | blocks | " +
      "bold italic backcolor | alignleft aligncenter " +
      "alignright alignjustify | bullist numlist outdent indent | " +
      "removeformat | help",
    content_style:
      "body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }",
    skin: "oxide",
    content_css: false,
    setup: function (editor) {
      editor.on("change", function () {
        editor.save();
      });
    },
  });
}

// Advanced configuration for job descriptions and detailed content
function initAdvancedEditor(selector) {
  tinymce.init({
    selector: selector,
    height: 400,
    menubar: true,
    plugins: [
      "advlist",
      "autolink",
      "lists",
      "link",
      "image",
      "charmap",
      "preview",
      "anchor",
      "searchreplace",
      "visualblocks",
      "code",
      "fullscreen",
      "insertdatetime",
      "media",
      "table",
      "help",
      "wordcount",
      "emoticons",
    ],
    toolbar:
      "undo redo | blocks fontsize | " +
      "bold italic underline strikethrough | forecolor backcolor | " +
      "alignleft aligncenter alignright alignjustify | " +
      "bullist numlist outdent indent | link image media table | " +
      "code preview fullscreen | help",
    content_style:
      "body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }",
    skin: "oxide",
    content_css: false,
    image_advtab: true,
    image_title: true,
    automatic_uploads: false, // Disable automatic uploads for security
    file_picker_types: "image",
    setup: function (editor) {
      editor.on("change", function () {
        editor.save();
      });
    },
  });
}

// Minimal configuration for short text areas (job titles, summaries)
function initMinimalEditor(selector) {
  tinymce.init({
    selector: selector,
    height: 150,
    menubar: false,
    plugins: ["lists", "link", "autolink"],
    toolbar: "bold italic underline | bullist numlist | link | removeformat",
    content_style:
      "body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }",
    skin: "oxide",
    content_css: false,
    setup: function (editor) {
      editor.on("change", function () {
        editor.save();
      });
    },
  });
}

// Initialize editors based on page content
document.addEventListener("DOMContentLoaded", function () {
  // Initialize basic editors for cover letters and general text
  initBasicEditor(".tinymce-basic");

  // Initialize advanced editors for job descriptions and detailed content
  initAdvancedEditor(".tinymce-advanced");

  // Initialize minimal editors for short text fields
  initMinimalEditor(".tinymce-minimal");
});
