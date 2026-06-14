/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

// public/js/quiz.js

async function soumettreEvaluation(evaluationId) {
    const form = document.querySelector(`#form-quiz-${evaluationId}`);
    if (!form) return;

    // Check if all questions are answered
    const questionNames = new Set([...new FormData(form).keys()].filter(k => k.startsWith('question_')));
    const questionsCount = form.querySelectorAll('.quiz-question').length;
    
    if (questionNames.size < questionsCount) {
        alert("Veuillez repondre a toutes les questions.");
        return;
    }

    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="button"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Soumission en cours...';
    }

    // Get base URL (assuming it's set in a global variable or deriving it)
    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/LMS_CAMEROUN/TP_LMS';
    const actionUrl = `${baseUrl}/index.php?page=etudiant/submit_quiz`;

    const data = await fetchAjax(actionUrl, {
        method: 'POST',
        body: formData
    });

    const resultContainer = document.querySelector('#resultat-quiz');
    if (!resultContainer) return;

    if (data.success) {
        // Disable form inputs
        form.querySelectorAll('input').forEach(input => input.disabled = true);
        if (submitBtn) submitBtn.style.display = 'none';


/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

        if (data.valide) {
            resultContainer.innerHTML = `
                <div class="alert alert-success">
                    <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                    <div>
                        <h3 class="font-bold">Félicitations !</h3>
                        <p>Vous avez obtenu la note de <strong>${data.score}%</strong>. Cette leçon est validée.</p>
                        ${data.certificat_genere ? `<p class="mt-2 text-success font-bold"><svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg> Vous avez terminé ce module ! Votre certificat a été généré.</p>` : ''}
                        <div class="mt-3">
                            <a href="${baseUrl}/index.php?page=etudiant/cours&id=${data.cours_id}" class="btn btn-primary btn-sm">Retour au plan du cours</a>
                        </div>
                    </div>
                </div>`;
            
            // Update progress bar
            if (window.updateProgressBar) {
                window.updateProgressBar('course-progress', data.nouvelle_progression);
            }
        } else {
            const btnRetry = data.tentatives_restantes > 0 
                ? `<button class="btn btn-secondary btn-sm mt-2" onclick="location.reload()">Réessayer le test</button>`
                : `<p class="text-error mt-2 font-bold">Nombre maximal de tentatives atteint. La leçon est verrouillée.</p>`;

            resultContainer.innerHTML = `
                <div class="alert alert-error flex-column align-start">
                    <div class="d-flex align-center gap-3">
                        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                        <div>
                            <h3 class="font-bold">Échec</h3>
                            <p>Votre note : <strong>${data.score}%</strong> (Seuil requis : ${data.seuil}%).</p>
                        </div>
                    </div>
                    ${btnRetry}
                </div>`;
        }
    } else {
        resultContainer.innerHTML = `
            <div class="alert alert-error">
                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                <p>${data.message || 'Une erreur est survenue.'}</p>
            </div>`;
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Soumettre';
        }
    }
}
