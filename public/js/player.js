// public/js/player.js

document.addEventListener('DOMContentLoaded', () => {
    const videoElement = document.getElementById('lesson-video');
    const pdfIframe = document.getElementById('lesson-pdf');
    const startQuizBtn = document.getElementById('btn-start-quiz');
    const quizSection = document.getElementById('quiz-section');
    
    const leconId = document.querySelector('meta[name="lecon-id"]')?.content;
    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/LMS_CAMEROUN/TP_LMS';

    let lessonViewed = false;

    async function markViewed() {
        if (lessonViewed || !leconId) return;
        lessonViewed = true;
        
        // Notify server
        const formData = new FormData();
        formData.append('lecon_id', leconId);
        // Add CSRF if available in meta
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (csrfToken) formData.append('csrf_token', csrfToken);

        const res = await fetchAjax(`${baseUrl}/index.php?page=etudiant/lecon_viewed`, {
            method: 'POST',
            body: formData
        });

        // Enable quiz button
        if (startQuizBtn) {
            startQuizBtn.disabled = false;
            startQuizBtn.classList.remove('btn-disabled');
            startQuizBtn.classList.add('btn-primary');
            startQuizBtn.textContent = "Démarrer l'évaluation";
        }
    }

    if (videoElement) {
        videoElement.addEventListener('ended', markViewed);
        // Backup: if they watch 90% of it
        videoElement.addEventListener('timeupdate', () => {
            if (!lessonViewed && videoElement.duration > 0) {
                if (videoElement.currentTime / videoElement.duration > 0.9) {
                    markViewed();
                }
            }
        });
    } else if (pdfIframe) {
        // For PDF, we can't easily track scrolling cross-origin if it's external,
        // but if it's local we might. Simple fallback: enable after a timeout (e.g., 5 seconds)
        // or just consider it viewed immediately upon load for simplicity in this project.
        setTimeout(markViewed, 5000); 
    }

    if (startQuizBtn && quizSection) {
        startQuizBtn.addEventListener('click', () => {
            startQuizBtn.style.display = 'none';
            quizSection.classList.remove('d-none');
        });
    }
});
