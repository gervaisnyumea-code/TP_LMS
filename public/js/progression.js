/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

// public/js/progression.js

window.updateProgressBar = function(elementId, percentage) {
    const container = document.getElementById(elementId);
    if (!container) return;


/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

    const bar = container.querySelector('.progress-bar');
    const text = container.querySelector('.progress-text');

    if (bar) {
        bar.style.width = `${percentage}%`;
        if (percentage === 100) {
            bar.classList.add('complete');
        }
    }
    
    if (text) {
        text.textContent = `${percentage}%`;
    }
};
