/**
 * SECEL - Main JavaScript
 * Animations: GSAP
 */

document.addEventListener('DOMContentLoaded', () => {
    // Initialisation du Loader GSAP
    const tl = gsap.timeline();

    tl.to("#loader-progress", {
        width: "100%",
        duration: 1.5,
        ease: "power2.inOut"
    })
    .to("#loader-wrapper", {
        opacity: 0,
        y: -50,
        duration: 0.5,
        delay: 0.2,
        display: "none"
    })
    .fromTo("#app-main", 
        { display: "none", opacity: 0 },
        { display: "flex", opacity: 1, duration: 0.5 },
        "-=0.3"
    )
    .from(".sidebar", {
        x: -50,
        opacity: 0,
        duration: 0.5,
        ease: "power2.out"
    }, "-=0.2")
    .from(".main-content > *", {
        y: 20,
        opacity: 0,
        duration: 0.4,
        stagger: 0.1,
        ease: "power2.out"
    }, "-=0.3");
});

// Fonctions utilitaires pour feedbacks
const showMessage = (msg, type = 'info') => {
    // Implémentation future de toasts/alertes simples
    alert(msg);
};
