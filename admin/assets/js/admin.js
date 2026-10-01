/**
 * NovaMart Admin Dashboard JS
 */

document.addEventListener('DOMContentLoaded', () => {
    initImagePreviews();
});

// Image preview helper for file inputs
function initImagePreviews() {
    document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
        input.addEventListener('change', (e) => {
            const previewId = input.dataset.preview;
            const previewImg = document.getElementById(previewId);
            if (previewImg && input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = (re) => {
                    previewImg.src = re.target.result;
                    previewImg.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        });
    });
}

// Global AJAX Confirm Action
async function confirmAction(url, message = 'Are you sure you want to perform this action?') {
    if (!confirm(message)) return false;
    window.location.href = url;
}
